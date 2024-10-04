<?php defined('BILLINGMASTER') or die; 
require_once ("{$this->layouts_path}/head.php");?>

<body id="page">
    <?=System::renderContent($text_lp);?>
    <?require_once ("{$this->layouts_path}/tech-footer.php");
    echo $product["$text_bottom"];?>
</body>
</html>