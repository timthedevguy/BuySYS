<?php
namespace AppBundle\Model;

class DefaultSettingsModel
{
    protected $maintenanceMode;

    public function setMaintenanceMode($maintenanceMode)
    {
        $this->maintenanceMode = $maintenanceMode;

        return $this;
    }

    public function getMaintenanceMode()
    {
        return $this->maintenanceMode;
    }
}
