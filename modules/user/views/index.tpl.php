<?php if ($unreads = $this->model('messages')->get_messages_unreads()): ?>
<a href="<?php echo url('user/messages') ?>" class="btn btn-primary btn-block"><?php echo $this->lang('You have %d unread message!|You have %d unread messages!', $unreads, $unreads) ?></a>
<?php else: ?>
<?php echo $this->lang('No new messages...') ?>
<?php endif ?>
