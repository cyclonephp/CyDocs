<html>
<head>
    <title><?= $manual->title ?></title>
    <link rel="stylesheet" type="text/css" href="stylesheet.css"/>
</head>
<body class="manual">

<h1><?= $manual->title ?></h1>

<h2>Table of Contents</h2>

<?= cyclone\view\PHPView::factory('cydocs/manual/toc', array(
    'sections' => $manual->sections
))->render() ?>

<?= $manual->text ?>

<? foreach ($manual->sections as $section)
        echo $section->render(); ?>

</body>
</html>