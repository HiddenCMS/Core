<?php

namespace HB\Modules\User\Models;

use HB\HiddenCMS\Loadables\Model;
use InvalidArgumentException;
use Throwable;

class Fields extends Model
{
	public function types()
	{
		return ['text' => 'Texte', 'select' => 'Liste de choix', 'radio' => 'Choix unique', 'switch' => 'Interrupteur', 'checkbox' => 'Cases à cocher'];
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
		if ($identifier === 'username') return 'Pseudo';
		if ($identifier === 'email') return 'Adresse e-mail';
		$field = $this->find(substr($identifier, 6));
		return $field ? utf8_htmlentities($field['label']) : 'Identifiant';
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
				$options = $field['type'] === 'switch' ? ['1' => 'Oui'] : $field['options'];
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
			$form->rule($form->form_password('custom_current_password')->title('Mot de passe actuel (si changement de l’identifiant)')->value('')
				->check(function($post) use ($old, $name, $user){
					if (is_string($post[$name] ?? NULL) && $this->decode($post[$name]) !== $old && (empty($post['custom_current_password']) || !$user->password($post['custom_current_password'])))
					{
						return 'Le mot de passe actuel est requis pour changer votre identifiant.';
					}
				}));
		}
		return $form
			->success(function($user, $form){
				try { $this->save_user($user); }
				catch (InvalidArgumentException $e) { $form->error($e->getMessage()); return; }
				notify('Champs personnalisés enregistrés');
				refresh();
			})
			->submit('Enregistrer')->panel()->title('Champs personnalisés', 'fas fa-list');
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
			throw new InvalidArgumentException('Valeur invalide pour le champ '.utf8_htmlentities($field['label']).'.');
		}
		if ($multiple)
		{
			foreach ($value ?: [] as $choice)
			{
				if (!is_string($choice) && !is_int($choice)) throw new InvalidArgumentException('Choix invalide.');
			}
		}
		$value = $multiple ? array_values(array_unique($value ?: [])) : $this->decode($value ?? '');
		$login = $this->identifier() === 'field:'.$field['id'];
		if (($field['required'] || $login) && ($value === '' || $value === []))
		{
			throw new InvalidArgumentException('Le champ '.utf8_htmlentities($field['label']).' est obligatoire.');
		}
		if ($field['type'] === 'text')
		{
			if (mb_strlen($value) > 190) throw new InvalidArgumentException('Le texte est limité à 190 caractères.');
			if ($login && $value !== '' && !$this->db->from('user_field_value')->where('field_id', $field['id'])
				->where('login_value', $value)->where('user_id <>', (int)$user_id)->empty())
			{
				throw new InvalidArgumentException('Cet identifiant est déjà utilisé.');
			}
		}
		else
		{
			$options = $field['type'] === 'switch' ? ['1' => 'Oui'] : $field['options'];
			foreach ($multiple ? $value : ($value === '' ? [] : [$value]) as $choice)
			{
				if (!is_scalar($choice) || !array_key_exists($choice, $options)) throw new InvalidArgumentException('Choix invalide.');
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
						throw new InvalidArgumentException('Ce pseudo ou cette adresse e-mail est déjà utilisé.');
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
					throw new InvalidArgumentException('Choisissez un pseudo, une adresse e-mail ou un champ texte.');
				}
				$id = (int)$field['id'];
				$missing = $this->db->query('SELECT u.id FROM user u LEFT JOIN user_field_value v ON v.user_id=u.id AND v.field_id='.$id.' WHERE u.deleted=\'0\' AND (v.value IS NULL OR v.value IN (\'""\', \'null\')) LIMIT 1')->row();
				if ($missing) throw new InvalidArgumentException('Renseignez ce champ pour chaque utilisateur actif avant de le choisir pour la connexion.');
				$duplicate = $this->db->query('SELECT COUNT(*) AS n FROM user_field_value v JOIN user u ON u.id=v.user_id AND u.deleted=\'0\' WHERE v.field_id='.$id.' GROUP BY CONVERT(JSON_UNQUOTE(v.value) USING utf8mb4) COLLATE utf8mb4_unicode_ci HAVING COUNT(*) > 1 LIMIT 1')->row();
				if ($duplicate) throw new InvalidArgumentException('Ce champ contient des doublons. Chaque utilisateur doit avoir une valeur unique.');
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
			if ($id && !$existing) throw new InvalidArgumentException('Champ introuvable.');
			$name = $existing ? $existing['name'] : $this->decode($data['name'] ?? '');
			$type = $existing ? $existing['type'] : ($data['type'] ?? '');
			$label = $this->decode($data['label'] ?? '');
			if (!preg_match('/^[a-z][a-z0-9_]{0,59}$/', $name)) throw new InvalidArgumentException('Nom technique invalide (lettres minuscules, chiffres et underscores).');
			if (!isset($this->types()[$type]) || !$label || mb_strlen($label) > 100) throw new InvalidArgumentException('Libellé ou type de champ invalide.');
			if (!$this->db->from('user_field')->where('name', $name)->where('id <>', (int)$id)->empty()) throw new InvalidArgumentException('Ce nom technique existe déjà.');
			$options = [];
			if (in_array($type, ['select', 'radio', 'checkbox'], TRUE))
			{
				foreach (preg_split('/\R/', $this->decode($data['options'] ?? '')) as $line)
				{
					if (trim($line) === '') continue;
					$parts = array_map('trim', explode('|', $line, 2));
					if (count($parts) !== 2 || !preg_match('/^[a-zA-Z0-9_-]{1,60}$/', $parts[0]) || $parts[1] === '' || isset($options[$parts[0]])) throw new InvalidArgumentException('Chaque choix doit être unique, au format valeur|libellé.');
					$options[$parts[0]] = $parts[1];
				}
				if (!$options) throw new InvalidArgumentException('Ajoutez au moins un choix.');
				if ($existing)
				{
					foreach ($this->db->from('user_field_value')->where('field_id', $id)->get() as $row)
					{
						foreach ((array)json_decode($row['value'], TRUE) as $choice)
						{
							if ($choice !== '' && !isset($options[$choice])) throw new InvalidArgumentException('Un choix utilisé par un utilisateur ne peut pas être retiré.');
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
			if ($this->identifier() === 'field:'.$id) throw new InvalidArgumentException('Ce champ est utilisé pour la connexion. Choisissez un autre identifiant avant de le supprimer.');
			$this->db->where('field_id', $id)->delete('user_field_value');
			$this->db->where('id', $id)->delete('user_field');
		});
	}
}
