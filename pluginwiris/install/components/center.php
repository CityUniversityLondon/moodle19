    <div id="center">
    <?php
        if (!defined('SECURITY_CONSTANT')) exit;

        $toInclude = '0';

        if (isset($_POST['step'])) {
            $toInclude = './install/pages/' . basename($_POST['step']) . '.php';
        }

        if (is_file($toInclude)) {
            require_once($toInclude);
        } else {
            require_once('./install/pages/0.php');
        }
    ?>
    </div>