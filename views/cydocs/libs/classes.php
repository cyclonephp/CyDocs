<html>
    <head>
        <link type="text/css" rel="stylesheet" href="stylesheet.css"/>
    </head>
    <body>
<h1>Classes</h1>
<ul class="classes">
    <? foreach ($classes as $name => $url) : ?>
    <li><a href="<?= $url ?>" target="frm-content"><?= $name ?></a></li>
    <? endforeach; ?>
</ul>
    </body>
</html>
