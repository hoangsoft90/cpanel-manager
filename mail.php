<?php
include_once ('includes/common/require.php');
_load_class('cpanel');

//check user login
check_user_session();

global $DB;

//list rows
$accts = list_whm_accts();
?>
<html>
<head>
    <title>Email</title>
    <?php include ('template/head.php');?>
    <script>
        jQuery(document).ready(function() {
            $('.list-accts .table-accts').hw_search_table({inputText: '#search_acct', column:1});
        });

    </script>
</head>
<body>
<div class="wrapper">
    <?php
    //include header
    include ('template/header.php');

    show_messages();
    ?>

    <div class="main">
        <table border="1px" cellpadding="3px" cellspacing="1px">
            <tr>
                <td>
                    <form method="post" data-require="acc" class="ajax-form" action="ajax.php?do=sendmail">
                        <p>
                            <label for="acc">Accounts</label><br/>
                            <select name="acc" class="combobox_acc">
                                <option value="">-----select-----</option>
                                <?php foreach($accts as $row){
                                    printf('<option value="%s">%s</option>', $row['id'], $row['cpanel_user'].'@'.$row['cpanel_host']);
                                }?>
                            </select>
                        </p>
                        <p>
                            <label for="subject">Subject</label><br/>
                            <input type="text" name="subject"/>
                        </p>
                        <p>
                            <label for="form">Form</label><br/>
                            <select name="form">
                                <option value="">-------select--------</option>
                                <option value="gui_tailieu">Gửi tài liệu hướng dẫn</option>
                                <option value="gui_thongtin_host">Gửi thông tin host</option>
                                <option value="wp_acc">Gửi tài khoản WP</option>
                                <option value="full_ftp_wpacc">Gửi FTP và WP account</option>
                            </select>
                        </p>
                        <p>
                            <label for="body">Body</label><br/>
                            <textarea name="body" cols="50" rows="10"></textarea>
                        </p>
                        <p>
                            <input type="submit" name="submit" value="Send"/>
                        </p>

                    </form>

                </td>
                <td valign="top">
                    <fieldset><legend>Send mail via Google Form</legend>
                        <a href="https://docs.google.com/forms/d/<?php echo TO_SEND_MAIL_GFORM_ID?>/viewform" target="_blank">View form</a><br/>
                        <a href="https://docs.google.com/forms/d/<?php echo TO_SEND_MAIL_GFORM_ID?>/edit" target="_blank">Edit Form</a><br/>
                    </fieldset>

                </td>
            </tr>
        </table>

    </div>
    <?php include ('template/footer.php')?>
</div>
</body>
</html>