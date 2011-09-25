<a name="method-<?= $method->name ?>"></a>
    <div class="method-details">
        <span class="modifiers"><?= $method->modifiers() ?> </span>
        <span class="type"><?= $method->return_type ?></span>
        <span class="method-name"><?= $method->name ?></span>
         (<?
         $first = TRUE;
         foreach ($method->parameters as $param) {
             if ( ! $first) echo ', ';
             $first = FALSE;
             echo $param->type . ' <code>';
             if ($param->by_ref) {
                 echo '<span class="byref">&</span>';
             }
             echo '$' . $param->name;
             if ($param->show_default) {
                 echo ' = ' . $param->default;
             }
             echo '</code>';
         }?>)
    <span class="method-descr">
        <?= $method->free_form_text ?>
    </span>
    <? if ( ! empty($method->thrown_exceptions)) : ?>
    <br/><strong>Thrown exceptions: </strong>
    <ul class="thrown-exc-list">
        <? foreach ($method->thrown_exceptions as $coderef => $descr) : ?>
        <li><?= $coderef . ' ' . $descr ?></li>
        <? endforeach; ?>
    </ul>
    <? endif; ?>
    <?= cyclone\View::factory('cydocs/libs/links', array('model' => $method))->render() ?>
</div>