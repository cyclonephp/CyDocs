<html>
    <head>
        <title><?= $class->name ?></title>
        <link type="text/css" rel="stylesheet" href="<?= $stylesheet_path ?>"/>
    </head>
    <body>
<?= $modifiers ?>
<h1>
    <?= $class->name ?>
</h1>
        <? if ($class->parent_class) : ?>
    extends <?= $class->parent_class ?>
    <? endif; ?>
<div class="free-form-text">
    <?= implode(' ', $class->comment->text) ?>
</div>
<? if ( ! empty($class->subclasses)) : ?>
<h4>Direct known subclasses: </h4>
    <ul>
<? foreach ($class->subclasses as $url => $subclass) : ?>
        <li><?= $subclass ?></li>
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
<h2>Property summary</h2>
<ul class="properties">
<? foreach ($class->properties as $prop) : ?>
    <li><a href="#prop-<?= $prop->name ?>" class="<?= $prop->visibility ?>"><?= $prop->name ?></a></li>
<? endforeach; ?>
</ul>
<? endif; ?>

<? if ( ! empty($class->methods)) : ?>
<h2>Method summary</h2>
<ul class="properties">
<? foreach ($class->methods as $method) : ?>
    <li><a href="#method-<?= $method->name ?>" class="<?= $method->visibility ?>"><?= $method->name ?></a></li>
<? endforeach; ?>
</ul>
<? endif; ?>



<? if ( ! empty($class->properties)) : ?>
<h2>Properties</h2>
<div class="properties">
<? foreach ($class->properties as $prop) : ?>
    <a name="prop-<?= $prop->name ?>"></a>
    <p class="prop-details">
    <span class="modifiers"><?= $prop->visibility ?></span>
    <span class="type"><?= $prop->type ?></span>
    <span class="prop-name"><?= $prop->name ?></span>
    <span class="prop-descr">
    <?= $prop->free_form_text ?>
        </span>
    </p>
<? endforeach; ?>
</div>
<? endif; ?>

<? if ( ! empty($class->methods)) : ?>
<h2>Methods</h2>
<div class="methods">
<? foreach ($class->methods as $method) echo View::factory('cydocs/libs/method', array('method' => $method));?>
    </div>
<? endif; ?>
 
    </body>
</html>