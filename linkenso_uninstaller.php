<!DOCTYPE HTML>
<html>
    <head>
        <title>�������� ������ LinkEnso PRO</title>
        <link rel="stylesheet" type="text/css" href="http://store.alaev.info/style.css" />
        <style type="text/css">
            #header {width: 100%; text-align: center;}
            .box-cnt{width: 100%; overflow: hidden;}
        </style>
    </head>

    <body>
        <div class="wrap">
            <div id="header">
                <h1>LinkEnso</h1>
            </div>
            <div class="box">
                <div class="box-t">&nbsp;</div>
                <div class="box-c">
                    <div class="box-cnt">
                        <?php

                            $output = module_uninstaller();
                            echo $output;

                        ?>
                    </div>
                </div>
                <div class="box-b">&nbsp;</div>
            </div>
        </div>
    </body>
</html>

<?php

    function module_uninstaller()
    {
        // ����������� �����
        $output = '<h2>����� ���������� � ������ ��� �������� ������ LinkEnso PRO!</h2>';
        $output .= '<p><strong>��������!</strong> ����� �������� ������ <strong>�����������</strong> ������� ���� <strong>linkenso_uninstaller.php</strong> � ������ �������!</p>';
        $output .= '<p>';
        $output .= '<strong>����� ����, ���������� ������� ��������� ����� � �����:</strong>';
        $output .= '<ul>';
            $output .= '<li>/engine/modules/<strong>linkenso.php</strong></li>';
            $output .= '<li>/engine/inc/<strong>linkenso.php</strong></li>';
            $output .= '<li>/engine/skins/images/<strong>linkenso.png</strong></li>';
            $output .= '<li>/templates/<em>��� ������ �������</em>/<strong>linkenso/</strong></li>';
        $output .= '</ul>';
        $output .= '</p>';

        // ���� ����� $_POST ��������� �������� linkenso_uninstall, ���������� �����������, �������� ����������
        if(!empty($_POST['linkenso_uninstall']))
        {
            // ���������� config
            include_once ('engine/data/config.php');

            // ���������� DLE API
            include ('engine/api/api.class.php');

            // ������� ������ �� �������
            $dle_api->uninstall_admin_module('linkenso');

            // �����
            $output .= '<p>';
            $output .= '������ ������� �����!';
            $output .= '</p>';
        }

        // ���� ����� $_POST ������ �� ���������, ������� ����� ��� �������� ������
        else
        {
            // �����
            $output .= '<p>';
            $output .= '<form method="POST" action="linkenso_uninstaller.php">';
            $output .= '<input type="hidden" name="linkenso_uninstall" value="1" />';
            $output .= '<input type="submit" value="������� ������" />';
            $output .= '</form>';
            $output .= '</p>';
        }
        
        $output .= '<p>';
        $output .= '<a href="http://alaev.info/blog/post/3982?from=LinkEnsoPro">���������� � ��������� ������</a>';
        $output .= '</p>';

        // ������� ���������� ��, ��� ������ ���� ��������
        return $output;
    }

?>
