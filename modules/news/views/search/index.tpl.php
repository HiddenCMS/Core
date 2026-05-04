<?php $news_module = $this->module('news'); ?>
<p class="float-right"><?php echo icon('far fa-bookmark').' <a href="'.url($news_module->category_path($category_id, $category)).'">'.$category.'</a> '.icon('fas fa-user').' '.($user_id ? $this->user->link($user_id, $username) : '<i>'.$this->lang('Visiteur').'</i>').' '.icon('far fa-clock').' '.time_span($date) ?></p>
<big><b><a href="<?php echo url($news_module->news_path($news_id, $title)) ?>"><?php echo $title ?></a></b></big>
<br />
<br />
<p><?php echo $introduction ?></p>
