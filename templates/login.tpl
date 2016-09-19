<!DOCTYPE html>
<html lang="{$lg}">
<head>
    <meta charset="utf-8">    
    <title>{$htmltitle|default:"aquarius cms"}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="./favicon.png" />
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style type="text/css">
    {literal}
    html, body { background: #f2f2f2;  }
    body { padding-top: 10%; }
    .container { width: 350px; }
    .container > .content { background-color: #fff; padding: 20px; margin: 0 -20px; -webkit-border-radius: 10px 10px 10px 10px; -moz-border-radius: 10px 10px 10px 10px; border-radius: 10px 10px 10px 10px; -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.15); -moz-box-shadow: 0 1px 2px rgba(0,0,0,.15); box-shadow: 0 1px 2px rgba(0,0,0,.15);}
    .login-form { margin-left: 40px; margin-right: 40px; }
    #loginlogo { margin: 50px 0 20px -25px }
    {/literal}
    </style>
    <script>{literal}
        function initLogin() {
            if (top != self) top.location = self.location;
            document.login.username.focus();
        }
    {/literal}</script>
</head>
<body onload="initLogin()" id="loginpage">
    <div class="container">
    <img src="picts/logo.png" alt="aquarius cms" id="loginlogo" />
    <div class="content">               
        {if $login_failed}
            <div class="alert alert-danger">
                {#login_failed#}
            </div>
        {/if}
        {if $cookie_missing}
            <div class="alert alert-danger">
                {#cookie_missing#}
            </div>
        {/if}  
        <div class="row">
          <div class="login-form">
              <h4>Login</h4>
                <form action="" method="post" name="login" enctype="multipart/form-data" class="form"  role="form">
                    <div class="form-group">
                        <input type="text" name="username" id="username" value="{$smarty.post.username|default:""|escape}" placeholder="{#login_username#}" class="form-control">
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" id="password" placeholder="{#login_password#}" class="form-control">
                    </div>            
                    <div class="form-group">
                        <button type="submit" value="Login" name="backend_login" class="btn btn-default">Login</button>  
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
</body>
</html>