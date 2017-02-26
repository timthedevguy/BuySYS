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
    
    protected $clientId;
    
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    
        return $this;
    }
    
    public function getClientId()
    {
        return $this->clientId;
    }

    protected $secretKey;

    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;

        return $this;
    }

    public function getSecretKey()
    {
        return $this->secretKey;
    }
}
