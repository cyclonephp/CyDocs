<html>
    <head>
        <title><?= $class->name ?></title>
        <link type="text/css" rel="stylesheet" href="<?= $stylesheet_path ?>"/>
    </head>
    <body>
<?= $modifiers ?>
<h1><?= $class->name ?></h1>
<div class="free-form-text">
    <?= implode(' ', $class->comment->text) ?>
</div>
<? if ( ! empty($class->subclasses)) : ?>
<h4>Direct known subclasses: </h4>
    <ul>
<? foreach ($class->subclasses as $subclass) : ?>
        <li><a href="?"><?= $subclass->name ?></a></li>
<? endforeach; ?>
    </ul>
<? endif; ?>
<? if ( ! empty($class->constants)) : ?>
<h2>Constants</h2>
<ul class="constants-cnt">
<? foreach ($class->constants as $name => $value) : ?>
    <li><?= $name . ' = ' . $value ?></li>
<? endforeach; ?>
</ul>
<? endif; ?>

<? if ( ! empty($class->properties)) : ?>
<h2>Properties</h2>
<ul class="properties">
<? foreach ($class->properties as $prop) : ?>
    <li><a href="#prop-<?= $prop->name ?>" class="<?= $prop->visibility ?>"><?= $prop->name ?></a></li>
<? endforeach; ?>
</ul>
<? endif; ?>

<? if ( ! empty($class->methods)) : ?>
<h2>Methods</h2>
<ul class="properties">
<? foreach ($class->methods as $method) : ?>
    <li><a href="#method-<?= $method->name ?>" class="<?= $method->visibility ?>"><?= $method->name ?></a></li>
<? endforeach; ?>
</ul>
<? endif; ?>



<? if ( ! empty($class->properties)) : ?>
<h2>Properties</h2>
<ul class="properties">
<? foreach ($class->properties as $prop) : ?>
    <span class="modifiers"><?= $prop->visibility ?></span>
    <h3><?= $prop->name ?></h3>
    <?= $prop->comment ?>
<? endforeach; ?>
</ul>
<? endif; ?>
 
    </body>
</html>