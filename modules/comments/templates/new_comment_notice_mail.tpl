{assign var=subject value=#new_comment_notice#}

{#new_comment_notice_text#}
{* Doth this work still? *}
http://{$smarty.server.SERVER_NAME}/aquarius/admin/index.php?lg=de&display=menu_comments

{#comment_subject#}: {$new_comment->subject|strip_tags|html_entity_decode}
{#comment_by#}: {$new_comment->name|strip_tags|html_entity_decode} {$new_comment->email|strip_tags|html_entity_decode}
