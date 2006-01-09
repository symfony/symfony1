<?php

class configSettingsMaxForwardsActions extends sfActions
{
  public function executeSelfForward()
  {
    $this->forward('configSettingsMaxForwards', 'selfForward');
  }
}

?>