<html>
<head>
    <title><?= $manual->title ?></title>
    <link rel="stylesheet" type="text/css" href="stylesheet.css"/>
</head>
<body>

<h1><?= $manual->title ?></h1>

<h2>Table of Contents</h2>

<?= cyclone\View::factory('cydocs/manual/toc', array(
    'sections' => $manual->sections
))->render() ?>

<?= nl2br($manual->text) ?>

<? foreach ($manual->sections as $section)
        echo $section->render(); ?>

</body>
</html>