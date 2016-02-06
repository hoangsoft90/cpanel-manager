<?php
require_once('includes/common/require.php');

// Now we handle the presentation, based on whether we are logged in or not.
// Nothing fancy, except where we create the 'login'-nonce towards the end
// while generating the login form.

header('Content-Type: text/html; charset=UTF-8');
?>
<html>
    <head>
        <title>Dashboard Cpanel</title>
        <?php include ('template/head.php')?>
    </head>
<body>
<div class="wrapper">
<?php
    //include header
    include ('template/header.php');

    show_messages();

?>
    <div class="main">
<?php
if (isAppLoggedIn()){
    ?>

<div class="hw-box cpanel-icon"><a class="tooltip" title="Quản lý tài khoản cpanel" href="account.php"></a></div>
<div class="hw-box database-icon"><a class="tooltip" title="Quản lý database" href="database.php"></a></div>
<div class="hw-box ftp-icon"><a class="tooltip" title="Quản lý FTP" href="ftp.php"></a></div>
<div class="hw-box fileman-icon"><a class="tooltip" title="Quản lý Files" href="fileman.php"></a></div>
<div class="hw-box wp-icon"><a class="tooltip" title="Quản lý Wordpress" href="wp.php"></a></div>
<div class="hw-box email-icon"><a class="tooltip" title="Quản lý gửi Email" href="mail.php"></a></div>
<div class="hw-box svn_subversion-icon"><a class="tooltip" title="SVN Subversion (http://svn.hoangweb.vn/)" href="svn.php"></a></div>

<?php
} else {
    ?>
    <p>Vui lòng đăng nhập vào hệ thống trước khi sử dụng tính năng.</p>
<?php
}
?>
    </div>
</div>
</div>
</body>
</html>