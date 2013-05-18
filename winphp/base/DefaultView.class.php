<?php 
class DefaultView
{
    private $templateFile;
    private $local;
    private $data;
    
    public function DefaultView($view, $model)
    {
        $this->data = $model;
        $this->templateFile = $view;
    }
    
    public function render()
    {
        if (strstr($this->templateFile, "redirect:"))
        {
            $url = substr($this->templateFile, strlen("redirect:"));
            header("Location:".$url);
            return "";
        }
        else if (strstr($this->templateFile, "json:"))
        {
            $parameter = explode(':', $this->templateFile);
            $num = count($parameter);
            //example json:callback
            if ($num == 2)
            {
                $checkList = 'soso.com|qq.com';
                $callback = $parameter[1];                
            }
            //example json:soso.com|qq.com:callback
            else if ($num == 3)
            {
                $checkList = $parameter[1];
                $callback = $parameter[2];
            }
            else
            {
                throw new SystemException('json parameter error');
            }
            if($checkList&& !$_SERVER['HTTP_REFERER']){
                throw new SystemException("forbidden");
            }
            $preg = '/^http:\/\/[^\/?;]*\.('.$checkList.')(\/|$)/';
            $data = parse_url($_SERVER['HTTP_REFERER']);
            $check = "{$data['scheme']}://{$data['host']}";

            if(!empty($_SERVER['HTTP_REFERER']) && !preg_match($preg, $check))
            {
                throw new SystemException("forbidden");
            }

            if (!strlen($callback))
            {
                $callback = WinRequest::getParameter('callback');
            }

            $callback = preg_replace("/[^a-zA-Z0-9_]/", "", $callback);
            
            return $callback."(".json_encode($this->data).");";
        }
        else if (strstr($this->templateFile, "text:"))
        {
            $text = substr($this->templateFile, strlen("text:"));
            return $text;
        }
        else
        {
            return $this->getRenderOutput();
        }
    }
   
    private function getRenderOutput()
    {
        $template = DefaultViewSetting::getTemplate();
        if (!file_exists(DefaultViewSetting::getRootDir().$this->templateFile))
        {
            throw new SystemException("no this template:".$this->templateFile);
        }
        DefaultViewSetting::setTemplateSetting($template);
		//var_dump($this->data);
        $template->assign($this->data);

        return $template->fetch($this->templateFile);
    }
    
}



