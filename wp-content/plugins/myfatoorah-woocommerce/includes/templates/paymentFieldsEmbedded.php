<?php
if ($this->gateways['form']) {
    return include_once('sectionForm.php');
}
?>
<script>
    jQuery('.payment_method_myfatoorah_embedded').remove();
</script>
