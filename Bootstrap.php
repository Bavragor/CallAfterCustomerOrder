<?php

use Shopware\Models\Config\Form;

class Shopware_Plugins_Frontend_CallAfterCustomerOrder_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.0.0';
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        return [
            'version' => $this->getVersion(),
            'autor' => 'Kevin Mauel <kevin.mauel2@gmail.com>',
            'label' => $this->getLabel(),
            'source' => 'Community',
            'description' => 'Calls a configurable url whenever an user finishes checkout and creates an order',
            'license' => 'MIT',
            'copyright' => 'Copyright © ' . date('Y') . ', Kevin Mauel'
        ];
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Calls a configurable url whenever an user finishes checkout and creates an order';
    }

    /**
     * Register the autoloader
     */
    public function afterInit()
    {
        $this->get('loader')->registerNamespace('Shopware\CallAfterCustomerOrder', __DIR__ . DIRECTORY_SEPARATOR);
    }

    /**
     * Installs the plugin
     *
     * @return bool
     */
    public function install()
    {
        $this->createForm($this->Form());

        // Whenever an order confirmation mail would be sent
        $this->subscribeEvent(
            'Shopware_Modules_Order_SendMail_BeforeSend',
            'onOrderFinish'
        );

        return [
            'success' => true
        ];
    }

    public function createForm(Form $form)
    {
        $form->setElement('text', 'url', [
            'label' => 'Url which will be called',
            'required' => true,
        ]);
    }

    public function enable()
    {
        return [
            'success' => true,
        ];
    }

    public function disable()
    {
        return [
            'success' => true
        ];
    }

    public function getCapabilities()
    {
        return [
            'install' => true,
            'update' => true,
            'enable' => true,
            'secureUninstall' => true
        ];
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        $this->secureUninstall();

        return [
            'success' => true
        ];
    }

    /**
     * @return bool
     */
    public function secureUninstall()
    {
        return true;
    }

    /**
     * Calls a configurable url whenever an user finishes checkout and creates an order
     * @param Enlight_Event_EventArgs $args
     */
    public function onOrderFinish(Enlight_Event_EventArgs $args)
    {
        $client = Shopware()->Container()->get('http_client');

        // Will return an response, but currently not used
        try {
            $client->post(
                trim($this->Config()->get('url')),
                ['Content-Type' => 'application/json'],
                json_encode(['orderNumber' => $args->get('context')['sOrderNumber']])
            );
        } catch(\Exception $exception) {
            Shopware()->Container()->get('pluginlogger')->addCritical($exception->getPrevious()->getRequest()->getUrl());
            Shopware()->Container()->get('pluginlogger')->addCritical($exception->getPrevious()->getRequest()->getBody());
            Shopware()->Container()->get('pluginlogger')->addCritical($args->get('context')['sOrderNumber']);
        }
    }
}
