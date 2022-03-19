<?php
    
    $conversion =json_decode(file_get_contents("https://graph.facebook.com/v4.0/101772948278269/conversations?user_id=3077032042420214&access_token=EAAK481QSfNIBABjWmqZATVtDo2OndEC56l59ITB6FNuZC5r0HHJmx8wfjMOKmt2IWYZB1AKgFdd2D56SilgpZARdzOARntrpmZAwrMA3GQZC8hX6BXCC2IJJrGBc7UTSk5ZCmfFAnE58perctauYRmTI7QIQUZAB8R279ZAbEzet7xi0sItvKKIHu"));
    var_dump($conversion->data[0]->link);
    
    ?>
