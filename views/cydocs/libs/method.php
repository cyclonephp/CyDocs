<a name="method-<?= $method->name ?>"></a>
    <p class="method-details">
        <span class="modifiers"><?= $method->visibility ?></span>
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
</p>