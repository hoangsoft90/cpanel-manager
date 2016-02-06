<?php
require_once('includes/common/require.php');
?>
<div class="header">
    <a class="button" href="index.php">Dashboard</a> |
    <a class="button" href="account.php">Account</a> |
    <a class="button" href="database.php">Database</a> |
    <a class="button" href="ftp.php">FTP</a> |
    <a class="button" href="fileman.php">File</a> |
    <a class="button" href="wp.php">Wordpress</a> |
    <a class="button" href="mail.php">Email</a> |
    <a class="button" href="help.php">Help</a> |
    <a class="button" href="svn.php">Subversion</a> |
    <div class="header-right">
        <?php if (isAppLoggedIn()){?>
        You are logged in, <strong><?php echo($_SESSION['username']);?></strong>
            <form action="login.php" class="inline-form" method="POST"><input type="hidden" name="action" value="logout"><input type="submit" value="Logout"></form>
            <form action="login.php" class="inline-form" method="POST"><input type="hidden" name="action" value="delete"><input type="submit" value="Delete account"></form>
        <?php }else{?>
            <a href="login.php">Sign-up|Sign-in</a>
        <?php }?>
    </div>
</div>
<!-- logging -->
<h2>Logger</h2>
<div class="logging">
    <span class="button-tog clearbtn"><a href="javascript:void(0)">Clear</a></span>
    <div class="content"></div>
</div>