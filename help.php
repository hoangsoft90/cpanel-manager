<?php
include_once ('includes/common/require.php');

//check user login
check_user_session();
?>
<html>
<head>
    <title>Database Cpanel</title>
    <?php include ('template/head.php');?>
</head>
<body>
<div class="wrapper">
    <?php
    //include header
    include ('template/header.php');

    show_messages();
    ?>

    <div class="main">
        <h2>Backup (Sao chép từ cpanel này sang cpanel khác)</h2>
        <p>
            <ul>
                <li>- Tải file backup cpmove-{$user}.tar.gz, từ cpanel gốc và cpanel khác. Sau đó giải nén 2 files sử dụng cygwin.</li>
                <li>- Sao chép các files trong /public_html và /mysql => sửa đổi cho phù hợp với cpanel muốn khôi phục. Ie: đổi URL trong .sql</li>
                <li>- Nén folder thành .tar.gz của cpanel muốn khôi phục.</li>
                <li>- Sử dụng lệnh `pscp` để upload file backup đã sửa lên thư mục /home</li>
            </ul>
        </p>
    </div>
</div>
</body>
</html>