<?php

namespace AppBundle\Controller;


use AppBundle\Entity\RuleEntity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends Controller {

	const STATUS_200_OK = 200;
	const STATUS_201_CREATED = 201;
	const STATUS_204_NO_CONTENT = 204;
	const STATUS_304_NOT_MODIFIED = 304;
	const STATUS_400_BAD_REQUEST = 400;
	const STATUS_401_UNAUTHORIZED = 401;
	const STATUS_403_FORBIDDEN = 403;
	const STATUS_404_NOT_FOUND = 404;
	const STATUS_500_SERVER_ERROR = 500;

	/**
	 * @Route("/api/{guid}/modifier", name="api-set-modifier")
	 */
	public function setModifierAction(Request $request, $guid)
	{
		$realGUID = $this->container->getParameter('api_guid');

		if ($guid == $realGUID)
		{
			if ($request->isMethod('POST'))
			{
				$data = \GuzzleHttp\json_decode($request->getContent());
				$keys = array();

				foreach ($data as $key => $value)
				{
					// Add to refresh list
					$keys[] = $key;

					$type = $this->getDoctrine()->getRepository('AppBundle:SDE\TypeEntity')
						->findOneByTypeID($key);
					$rule = $this->getDoctrine()->getRepository('AppBundle:RuleEntity')
						->findOneBy(array(
							'attribute' => 'modifier',
							'targetId'  => $key
						));

					if ($rule == null)
					{
						$rule = new RuleEntity();
						$rule->setTarget('item')
							->setTargetId($key)
							->setTargetName($type->getTypeName())
							->setAttribute('modifier')
							->setRuleType('P')
							->setSort($this->getDoctrine()->getRepository('AppBundle:RuleEntity')->getNextSort('P'));

						$this->getDoctrine()->getManager()->persist($rule);
					}

					if ($value > 0)
					{

						$rule->setValue($value);
						$this->getDoctrine()->getManager()->flush();
					} else
					{

						return new JsonResponse(array('message' => 'Modifier value must be greater than 0', $this::STATUS_400_BAD_REQUEST));
					}
				}

				return new JsonResponse(array('message' => 'OK'), $this::STATUS_200_OK);
			} else
			{
				return new JsonResponse(array('message' => 'Only accepts POST'), $this::STATUS_400_BAD_REQUEST);
			}
		} else
		{
			return new JsonResponse(array('message' => 'Not Authorized'), $this::STATUS_401_UNAUTHORIZED);
		}
	}


}