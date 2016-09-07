<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    private $tableGateway;

    private function getTableGateway() {
        if (!$this->tableGateway) {
            $this->tableGateway = $this->getServiceLocator()->get('Application\Model\BeerTableGateway');
        }
        return $this->tableGateway;
    }

    public function indexAction()
    {
        $beers = $this->getServiceLocator()
                      ->get('Application\Model\BeerTableGateway')
                      ->fetchAll();
        return new ViewModel(array('beers' => $beers));
    }

    public function insertAction()
    {
        $form = $this->getServiceLocator()->get('Application\Form\Beer');
        $tableGateway = $this->getServiceLocator()->get('Application\Model\BeerTableGateway');
        $beer = new \Application\Model\Beer;
        $request = $this->getRequest();
        $form->setAttribute('action', '/insert');
        $form->get('send')->setAttribute('value', 'Salvar');
        
        if ($request->isPost()) {
            
            $form->setInputFilter($beer->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                /* pega os dados validados e filtrados */
                $data = $form->getData();
                /* preenche os dados do objeto Post com os dados do formulário*/
                $beer->exchangeArray($data);
                /* salva o novo post*/
                $tableGateway->save($beer);
                /* redireciona para a página inicial que mostra todos os posts*/
                return $this->redirect()->toUrl('/');
            }
        }

        $id = (int) $this->params()->fromRoute('id', 0);
        //é uma atualização  
        if ($id > 0) {
            /* busca a entidade no banco de dados*/
            $beer = $tableGateway->get($id);

            /* preenche o formulário com os dados do\
            banco de dados*/
            $form->bind($beer);

            /* muda o texto do botão submit*/
            $form->get('send')->setAttribute('value', 'Editar');
        }

        return new ViewModel(['beerForm' => $form]);
    }

        /**
    * Exclui um post
    * @return void
    */
    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if ($id == 0) {
            throw new \Exception("Código obrigatório");
        }
        /* remove o registro e redireciona para a página inicial*/
        $tableGateway = $this->getTableGateway()->delete($id);
        
        return $this->redirect()->toUrl('/');
    }
}
