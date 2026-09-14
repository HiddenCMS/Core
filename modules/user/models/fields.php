<?php

namespace HB\Modules\User\Models;

use HB\HiddenCMS\Loadables\Model;
use InvalidArgumentException;
use Throwable;

class Fields extends Model
{
	public function types()
	{
		return ['text' => (string)$this->lang('Text'), 'select' => (string)$this->lang('Dropdown'), 'radio' => (string)$this->lang('Single choice'), 'switch' => (string)$this->lang('Toggle'), 'checkbox' => (string)$this->lang('Checkboxes')];
	}

	public function all()
	{
		$fields = $this->db->from('user_field')->order_by('id')->get();
		foreach ($fields as &$field)
		{
			$field['options'] = json_decode($field['options'], TRUE) ?: [];
		}
		return $fields;
	}

	public function find($id)
	{
		foreach ($this->all() as $field)
		{
			if ((int)$field['id'] === (int)$id) return $field;
		}
		return NULL;
	}

	public function identifier()
	{
		return $this->db->select('identifier')->from('user_login')->where('id', 1)->row() ?: 'username';
	}

	public function login_label()
	{
		$identifier = $this->identifier();
		if ($identifier === 'username') return (string)$this->lang('Username');
		if ($identifier === 'email') return (string)$this->lang('Email address');
		$field = $this->find(substr($identifier, 6));
		return $field ? utf8_htmlentities($field['label']) : (string)$this->lang('Login identifier');
	}

	public function login_user($value)
	{
		$identifier = $this->identifier();
		$query = $this->collection('user')->where('deleted', FALSE);
		if (!is_string($value) || trim($value) === '') return $query->where('id', 0)->row();
		if (in_array($identifier, ['username', 'email'], TRUE))
		{
			return $query->where($identifier, $value)->row();
		}
		$id = $this->db->select('user_id')->from('user_field_value')
			->where('field_id', (int)substr($identifier, 6))
			->where('login_value', $this->decode($value))->row();
		return $query->where('id', (int)$id)->row();
	}

	public function values($user_id)
	{
		$values = [];
		if ($user_id)
		{
			foreach ($this->db->from('user_field_value')->where('user_id', $user_id)->get() as $row)
			{
				$values['custom_'.$row['field_id']] = json_decode($row['value'], TRUE);
			}
		}
		return $values;
	}

	public function add_rules($form, $user)
	{
		$values = $this->values($user->id);
		$identifier = $this->identifier();
		foreach ($this->all() as $field)
		{
			$name = 'custom_'.$field['id'];
			$type = $field['type'] === 'switch' ? 'checkbox' : $field['type'];
			$rule = $form->{'form_'.$type}($name)->title(utf8_htmlentities($field['label']));
			if ($type !== 'text')
			{
				$options = $field['type'] === 'switch' ? ['1' => (string)$this->lang('Yes')] : $field['options'];
				$rule->data(array_map('utf8_htmlentities', $options));
				if ($field['type'] === 'switch') $rule->toggle();
				if ($type === 'select') $rule->search(0);
			}
			$rule->required_if($field['required'] || $identifier === 'field:'.$field['id']);
			$rule->check(function($post) use ($field, $user, $name){
				try { $this->validate_value($field, $post[$name] ?? NULL, $user->id); }
				catch (InvalidArgumentException $e) { return $e->getMessage(); }
			});
			$value = $values[$name] ?? ($type === 'checkbox' ? [] : '');
			$user->set($name, $type === 'text' ? utf8_htmlentities($value) : $value);
			$form->rule($rule);
		}
		return $form;
	}

	public function profile_panel($user)
	{
		if (!$this->all()) return '';
		$form = $this->module('user')->form2('custom_fields', $user);
		$identifier = $this->identifier();
		if (!$this->url->admin && strpos($identifier, 'field:') === 0)
		{
			$name = 'custom_'.substr($identifier, 6);
			$old = $this->values($user->id)[$name] ?? '';
			$form->rule($form->form_password('custom_current_password')->title((string)$this->lang('Current password (when changing the login identifier)'))->value('')
				->check(function($post) use ($old, $name, $user){
					if (is_string($post[$name] ?? NULL) && $this->decode($post[$name]) !== $old && (empty($post['custom_current_password']) || !$user->password($post['custom_current_password'])))
					{
						return (string)$this->lang('Your current password is required to change your identifier.');
					}
				}));
		}
		return $form
			->success(function($user, $form){
				try { $this->save_user($user); }
				catch (InvalidArgumentException $e) { $form->error($e->getMessage()); return; }
				notify((string)$this->lang('Custom fields saved'));
				refresh();
			})
			->submit((string)$this->lang('Save'))->panel()->title('Custom fields', 'fas fa-list');
	}

	private function decode($value)
	{
		return trim(html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
	}

	private function validate_value(array $field, $value, $user_id = 0)
	{
		$multiple = in_array($field['type'], ['checkbox', 'switch'], TRUE);
		if (($multiple && $value !== NULL && !is_array($value)) || (!$multiple && $value !== NULL && !is_string($value)))
		{
			throw new InvalidArgumentException((string)$this->lang('Invalid value for field %s.', utf8_htmlentities($field['label'])));
		}
		if ($multiple)
		{
			foreach ($value ?: [] as $choice)
			{
				if (!is_string($choice) && !is_int($choice)) throw new InvalidArgumentException((string)$this->lang('Invalid choice.'));
			}
		}
		$value = $multiple ? array_values(array_unique($value ?: [])) : $this->decode($value ?? '');
		$login = $this->identifier() === 'field:'.$field['id'];
		if (($field['required'] || $login) && ($value === '' || $value === []))
		{
			throw new InvalidArgumentException((string)$this->lang('Field %s is required.', utf8_htmlentities($field['label'])));
		}
		if ($field['type'] === 'text')
		{
			if (mb_strlen($value) > 190) throw new InvalidArgumentException((string)$this->lang('Text is limited to 190 characters.'));
			if ($login && $value !== '' && !$this->db->from('user_field_value')->where('field_id', $field['id'])
				->where('login_value', $value)->where('user_id <>', (int)$user_id)->empty())
			{
				throw new InvalidArgumentException((string)$this->lang('This identifier is already in use.'));
			}
		}
		else
		{
			$options = $field['type'] === 'switch' ? ['1' => (string)$this->lang('Yes')] : $field['options'];
			foreach ($multiple ? $value : ($value === '' ? [] : [$value]) as $choice)
			{
				if (!is_scalar($choice) || !array_key_exists($choice, $options)) throw new InvalidArgumentException((string)$this->lang('Invalid choice.'));
			}
		}
		return $value;
	}

	// The singleton lock serializes identifier changes and profile writes.
	private function locked(callable $callback)
	{
		$this->db->begin_transaction();
		try
		{
			$this->db->query('SELECT id FROM user_login WHERE id = 1 FOR UPDATE')->row();
			$result = $callback();
			$this->db->commit();
			return $result;
		}
		catch (Throwable $e)
		{
			$this->db->rollback();
			throw $e;
		}
	}

	public function save_user($user, $create = FALSE)
	{
		return $this->locked(function() use ($user, $create){
			$values = [];
			foreach ($this->all() as $field)
			{
				$values[$field['id']] = $this->validate_value($field, $user->{'custom_'.$field['id']}, $user->id);
			}
			if ($create)
			{
				foreach (['username', 'email'] as $key)
				{
					if (!$this->db->from('user')->where('deleted', FALSE)->where($key, $user->$key)->empty())
					{
						throw new InvalidArgumentException((string)$this->lang('This username or email address is already in use.'));
					}
				}
				$user->set('admin', FALSE)->set_password($user->password)->create();
			}
			$identifier = $this->identifier();
			foreach ($values as $id => $value)
			{
				$this->db->where('user_id', $user->id)->where('field_id', $id)->delete('user_field_value');
				$this->db->insert_checked('user_field_value', [
					'user_id' => $user->id, 'field_id' => $id,
					'value' => json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
					'login_value' => $identifier === 'field:'.$id ? $value : NULL
				]);
			}
			return $user;
		});
	}

	public function set_identifier($identifier)
	{
		$this->locked(function() use ($identifier){
			if (!in_array($identifier, ['username', 'email'], TRUE))
			{
				if (!preg_match('/^field:([1-9][0-9]*)$/', $identifier, $match) || !($field = $this->find($match[1])) || $field['type'] !== 'text')
				{
					throw new InvalidArgumentException((string)$this->lang('Choose a username, email address or text field.'));
				}
				$id = (int)$field['id'];
				$missing = $this->db->query('SELECT u.id FROM user u LEFT JOIN user_field_value v ON v.user_id=u.id AND v.field_id='.$id.' WHERE u.deleted=\'0\' AND (v.value IS NULL OR v.value IN (\'""\', \'null\')) LIMIT 1')->row();
				if ($missing) throw new InvalidArgumentException((string)$this->lang('Complete this field for every active user before choosing it for sign-in.'));
				$duplicate = $this->db->query('SELECT COUNT(*) AS n FROM user_field_value v JOIN user u ON u.id=v.user_id AND u.deleted=\'0\' WHERE v.field_id='.$id.' GROUP BY CONVERT(JSON_UNQUOTE(v.value) USING utf8mb4) COLLATE utf8mb4_unicode_ci HAVING COUNT(*) > 1 LIMIT 1')->row();
				if ($duplicate) throw new InvalidArgumentException((string)$this->lang('This field contains duplicates. Each user must have a unique value.'));
			}
			$this->db->execute_checked('UPDATE user_field_value SET login_value=NULL');
			if (isset($id))
			{
				$this->db->execute_checked('UPDATE user_field_value v JOIN user u ON u.id=v.user_id AND u.deleted=\'0\' SET v.login_value=JSON_UNQUOTE(v.value) WHERE v.field_id='.$id);
			}
			$this->db->execute_checked("UPDATE user_login SET identifier='".$this->db->escape_string($identifier)."' WHERE id=1");
		});
	}

	public function save_definition(array $data, $id = 0)
	{
		return $this->locked(function() use ($data, $id){
			$existing = $id ? $this->find($id) : NULL;
			if ($id && !$existing) throw new InvalidArgumentException((string)$this->lang('Field not found.'));
			$name = $existing ? $existing['name'] : $this->decode($data['name'] ?? '');
			$type = $existing ? $existing['type'] : ($data['type'] ?? '');
			$label = $this->decode($data['label'] ?? '');
			if (!preg_match('/^[a-z][a-z0-9_]{0,59}$/', $name)) throw new InvalidArgumentException((string)$this->lang('Invalid internal name (lowercase letters, numbers and underscores).'));
			if (!isset($this->types()[$type]) || !$label || mb_strlen($label) > 100) throw new InvalidArgumentException((string)$this->lang('Invalid field label or type.'));
			if (!$this->db->from('user_field')->where('name', $name)->where('id <>', (int)$id)->empty()) throw new InvalidArgumentException((string)$this->lang('This internal name already exists.'));
			$options = [];
			if (in_array($type, ['select', 'radio', 'checkbox'], TRUE))
			{
				foreach (preg_split('/\R/', $this->decode($data['options'] ?? '')) as $line)
				{
					if (trim($line) === '') continue;
					$parts = array_map('trim', explode('|', $line, 2));
					if (count($parts) !== 2 || !preg_match('/^[a-zA-Z0-9_-]{1,60}$/', $parts[0]) || $parts[1] === '' || isset($options[$parts[0]])) throw new InvalidArgumentException((string)$this->lang('Each choice must be unique, in value|label format.'));
					$options[$parts[0]] = $parts[1];
				}
				if (!$options) throw new InvalidArgumentException((string)$this->lang('Add at least one choice.'));
				if ($existing)
				{
					foreach ($this->db->from('user_field_value')->where('field_id', $id)->get() as $row)
					{
						foreach ((array)json_decode($row['value'], TRUE) as $choice)
						{
							if ($choice !== '' && !isset($options[$choice])) throw new InvalidArgumentException((string)$this->lang('A choice used by a user cannot be removed.'));
						}
					}
				}
			}
			$record = ['name' => $name, 'label' => $label, 'type' => $type, 'options' => json_encode($options, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), 'required' => !empty($data['required'])];
			if ($existing)
			{
				$this->db->where('id', $id)->update('user_field', $record);
				return $id;
			}
			return $this->db->insert_checked('user_field', $record);
		});
	}

	public function delete_definition($id)
	{
		$this->locked(function() use ($id){
			if ($this->identifier() === 'field:'.$id) throw new InvalidArgumentException((string)$this->lang('This field is used for sign-in. Choose another identifier before deleting it.'));
			$this->db->where('field_id', $id)->delete('user_field_value');
			$this->db->where('id', $id)->delete('user_field');
		});
	}
}
