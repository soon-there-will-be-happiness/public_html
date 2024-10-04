<?php defined('BILLINGMASTER') or die; ?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connect: <?=@ $title?>  Loading...</title>
    <link rel="stylesheet" type="text/css" href="/extensions/connect/web/css/load_page.css?<?=time()?>">
</head>

<body>

    <div class="container">
        <div class="loader"></div>
    </div>
    <h1><?= isset($message) ? $message : 'Loading...' ?></h1>

    <? if(isset($html)) print($html) ?>
    <span id='message'></span>

    <script type="text/javascript">
        var time = parseInt('<?= isset($timeout) ? $timeout : 30 ?>');

        history.pushState(null, null, window.location.href.split("?")[0]);

        setTimeout(function() {
            document.getElementById('message').innerHTML = 'Можете закрыть это окно.';
            window.close();
            close();
        }, 1000 *  time);
    </script>

</body>
</html>