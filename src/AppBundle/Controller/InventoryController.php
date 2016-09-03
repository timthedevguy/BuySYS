<?php
namespace AppBundle\Controller;

use AppBundle\Entity\InventoryEntity;
use AppBundle\Form\CompareInventoryForm;
use AppBundle\Form\InventoryForm;
use AppBundle\Model\BuyBackModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\LineItemEntity;
use AppBundle\Model\CompareItemModel;

class InventoryController extends Controller
{

    /**
     * @Route("/admin/inventory", name="admin_inventory")
     */
    public function indexAction(Request $request)
    {
        $inventory = $this->getDoctrine()->getRepository('AppBundle:InventoryEntity')->findAll();

        return $this->render('inventory/index.html.twig', array('page_name' => 'Inventory', 'sub_text' => 'Current Stock', 'inventory' => $inventory));
    }

    /**
     * @Route("/admin/inventory/subtact", name="subtract_inventory")
     */
    public function removeInventoryAction(Request $request)
    {
        $id = $request->query->get('id');

        $em = $this->getDoctrine()->getManager();
        $inv = $this->getDoctrine()->getRepository('AppBundle:InventoryEntity')->findOneById($id);

        if($inv->getQuantity() == 1)
        {
            $em->remove($inv);
            $em->flush();

            return $this->redirectToRoute('admin_inventory');
        }

        $inv->setCost($inv->getCost() - ($inv->getCost() / $inv->getQuantity()));
        $inv->setQuantity($inv->getQuantity() - 1);


        $em->flush();

        return $this->redirectToRoute('admin_inventory');
    }

    /**
     * @Route("/admin/inventory/delete", name="delete_inventory")
     */
    public function deleteInventoryAction(Request $request)
    {
        $id = $request->query->get('id');

        $em = $this->getDoctrine()->getManager();
        $inv = $this->getDoctrine()->getRepository('AppBundle:InventoryEntity')->findOneById($id);

        $em->remove($inv);
        $em->flush();
        return $this->redirectToRoute('admin_inventory');
    }

    /**
     * @Route("/admin/inventory/compare", name="compare_inventory")
     */
    public function compareInventoryAction(Request $request)
    {
        $items = new BuyBackModel();
        $form = $this->createForm(CompareInventoryForm::class, $items);
        $form->handleRequest($request);

        if($form->isValid() && $form->isSubmitted()) {

            $lineItems = $this->get('parser')->GetLineItemsFromPasteData($items->getItems());
            $results = array();

            if($this->get('market')->PopulateLineItems($lineItems, false, true)) {

                /* @var $item LineItemEntity */
                foreach($lineItems as $item) {

                    $model = new CompareItemModel();
                    $model->setTypeId($item->getTypeId());
                    $model->setName($item->getName());
                    $model->setUnitPrice($item->getMarketPrice());
                    $model->setRefinePrice($this->get("market")->GetMarketPriceByCompositionByTypeId($item->getTypeId()));

                    if($model->getUnitPrice() > $model->getRefinePrice()) {

                        $model->setDecision("Sell");
                    } else if($model->getUnitPrice() < $model->getRefinePrice()) {

                        $model->setDecision("Refine");
                    } else if($model->getUnitPrice() == $model->getRefinePrice()) {

                        $model->setDecision("Either");
                    }

                    $results[] = $model;
                }
            }

            return $this->render('inventory/compare.html.twig', array('page_name' => 'Compare', 'sub_text' => 'Sell or Reprocess',
                'items' => $results, 'form' => $form->createView()));
        }

        return $this->render('inventory/compare.html.twig', array('page_name' => 'Compare', 'sub_text' => 'Sell or Reprocess',
            'items' => null, 'form' => $form->createView()));
    }
    
    /**
     * @Route("/admin/inventory/ajax_create_inventory", name="ajax_create_inventory")
     */
    public function ajax_CreateInventoryAction(Request $request)
    {
        $inv = new InventoryEntity();
        $form = $this->createForm(InventoryForm::class, $inv);

        if($request->getMethod() == "POST")
        {
            $form_results = $request->request->get('inventory_form');
            $inv->setTypeId($form_results['typeid']);
            $inv->setQuantity($form_results['quantity']);
            $inv->setCost($form_results['cost']);
            $inv->setUser($this->getUser()->getUsername());
            $inv->setCreatedOn(new \DateTime());

            $type = $this->getDoctrine()->getRepository('EveBundle:TypeEntity', 'evedata')->findOneByTypeID($inv->getTypeId());

            $inv->setTypeName($type->getTypeName());

            $em = $this->getDoctrine()->getManager();
            $em->persist($inv);
            $em->flush();

            return $this->redirectToRoute('admin_inventory');
        }

        $results = $this->render('inventory/create.html.twig', array('form' => $form->createView()));
        return $results;
    }
}
