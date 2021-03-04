<?php
namespace Mageplaza\BetterPopup\Plugin;

use Magento\Framework\App\Request\Http;

class Subscriber {
    protected $request;
    public function __construct(Http $request){
        $this->request = $request;
    }

    public function aroundSubscribe($subject, \Closure $proceed, $email) {
        $result = "";
        if ($this->request->isPost()) {
            $submitby = $this->request->getPost('submitby');
            if(!isset($submitby)){
                $submitby = "Subscriber";
            }
            $subject->setSubmitby($submitby);
            $result = $proceed($email);

            try {
                $subject->save();
            }catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        return $result;
        }
    }
}