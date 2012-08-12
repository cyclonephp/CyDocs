<html>
    <head>
        <link type="text/css" rel="stylesheet" href="stylesheet.css"/>
    </head>
    <body>
<h1>Classes</h1>

<ul class="packages">
    <? foreach ($namespaces as $ns_name => $classlist) : ?>
    <li><?= $ns_name ?></li>
    <ul class="classes">
        <? foreach ($classlist as $classname => $url) : ?>
        <li><a href="<?= $url ?>" target="frm-content"><?= $classname ?></a></li>
        <? endforeach ?>
    </ul>
    <? endforeach; ?>
</ul>

    </body>
</html>
