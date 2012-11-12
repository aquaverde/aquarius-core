<?php

/** Send a notification mail when a new comment is posted.
  * 
  * Notification is sent only once until all new comments have been processed. */
$config['comments']['notice_email'] = false;

/** Sent notice to this address when it's used in the comment */
$config['comments']['notice_email_test'] = false;

/** Maximum length of generated subject lines, in characters */
$config['comments']['subject_length'] = 40;

/** How long rejected comments are kept (strtotime() format) */
$config['comments']['limbo_duration'] = '2 weeks';

/** Which comment form fields to carry in session */
$config['comments']['carry_fields'] = array('prename', 'name', 'email');

/** Use a different name for the email field.
  * To prevent spam, do not use the conventional name for the email field.
  *
  * If this is set to anything except false, and a field 'email' is POSTed with
  * content, the comment is not registered, but the module acts as if it was. */
$config['comments']['bend_email_field'] = false;
