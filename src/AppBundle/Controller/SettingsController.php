<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\SettingEntity;

class SettingsController extends Controller
{
    /**
     * @Route("/settings/buyback", name="settings_buyback")
     */
    public function buyBackAction(Request $request)
    {
        // Logic
    }
}
