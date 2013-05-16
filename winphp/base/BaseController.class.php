<?php 
class BaseController
{
    protected $interceptors = array();
    private $viewClass = "DefaultView";
    public function setViewClass($viewClass)
    {
        $this->viewClass = $viewClass;
    }
    public function addInterceptor($interceptor)
    {
        $this->interceptors[] = $interceptor;
    }
    
    /**
     can be override, select interceptors for an action
     by default, select all interceptors
     */
    public function loadIntercepters($actionName, $methodName)
    {
        return $this->interceptors;
    }

    
    public function process()
    {
        $mapper = WinRequest::getAttribute("mapper");
		
        $method = $mapper->getMethod();
        
        $executeInfo = array('controllerName'=>preg_replace("/[A-Z][a-z]+$/","",get_class($mapper->getController())), 
							'methodName'=>$method[1],
							'actionName'=>preg_replace("/[A-Z][a-z]+$/","",get_class($method[0])));
		WinRequest::mergeModel(array('executeInfo'=>$executeInfo));
		WinRequest::mergeModel(array('version'=>VERSION));
		WinRequest::mergeModel(array('isDebug'=>IS_DEBUG));
		
		$interceptors = $this->loadIntercepters($actionName, $methodName);
        try
        {
            foreach ($interceptors as $interceptor)
            {
                $interceptor->beforeAction();
            }
            list($view, $model) = $this->getViewAndModel(call_user_func($method));
            WinRequest::mergeModel($model);
            for($i=count($interceptors)-1;$i>=0;$i--)
            {
                $interceptor=$interceptors[$i];
                $interceptor->afterAction();
            }
        }
        catch(ModelAndViewException $e)
        {
            list($view, $model) = $this->getViewAndModel( $e->getModelAndView());
            WinRequest::setModel($model);
        }
        
        $viewObj = new $this->viewClass($view, WinRequest::getModel());
        return $viewObj->render();
    }
    
    private function getViewAndModel($modelAndView){
        if(isset($modelAndView['view'])){
            return array($modelAndView['view'],
                     $modelAndView['model']);
        }else{
            return $modelAndView;
        }
    }
}
