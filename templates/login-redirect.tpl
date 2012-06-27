<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>aquarius login redirect</title>
    {js} window.location = '{$correct_uri}'; {/js}
</head>
<body>
    <a target="_top" href='{$correct_uri}'>{#s_please_login#}</a>
</body>
</html>
