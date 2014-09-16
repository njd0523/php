<?php
require_once '/home/share/frontend/media/modules/base/renderers/MediaModulePatternRendererBase.php';

class MediaHashoutPanelists extends MediaModulePatternRendererBase
{
    public function render()
    {
        //Error handle for invalid data and device is smartphone won't display this module.
        if (!$this->data || $this->getContextValue('ctx.dimension.device')==='smartphone') return;
        
        $header      = $this->getFirstArgument('header');
        $per_page    = $this->getFirstArgument('per_page');
        $guid_list   = $this->getFirstArgument('GUIDList');
        $guid_list   = empty($guid_list) ?  null : json_decode($guid_list);
        $prev_button = $this->link($this->html('prev', 'div'), '#', 'button prev');
        $next_button = $this->link($this->html('next', 'div'), '#', 'button next');
        $bd_list     = $bd_bar_list = array();
        $item_div    = '';

        for ($i = 0; $i < count($this->data); $i++) 
        {        
            $item = $this->data->getItem($i);
            $guid = ($i < count($guid_list)) ? $guid_list[$i] : null;
            $img  = $item->getImage();
            $nick = $item->getNickname();

            $resizedImg = $this->getResizedImage( $img->imageUrl, 90, 90, 'resized');
            $resizedImg = array_merge($resizedImg, array('alt'=>$nick, 'title'=>$nick));

            $imgClass = ($guid->twitter_id) ? $guid->twitter_id : '';
            $imgDiv   = $this->html($this->link( $this->image( $resizedImg ), '#'), 'div', $imgClass) . $this->html('img reflection', 'div', 'ref');
           
            //Paging the items 
            $item_div .= $this->html($imgDiv, 'span', 'thumb '.$i);
            $bd_list[] = (($i+1) % $per_page == 0) ? array('content'=>$item_div) : '';
            $item_div  = (($i+1) % $per_page == 0) ? '' : $item_div;

            $logo  = empty($guid) ? null : $guid->logo;
            $logo  = empty($logo) ? null : $this->getResizedImage( $logo, 165, 35, 'resized');
            $logo  = empty($logo) ? null : array_merge($logo, array('alt'=>$nick, 'title'=>$nick));
            $logo  = empty($logo) ? null : $this->html( $this->image($logo), 'div',  'logo' );

            $fname = $item->getGivenName();
            $lname = $item->getFamilyName();
            $name  = ($fname && $lname)  ? $this->getTranslation('media/modules/election/strings/PANELISTS_NAME', array( $lname, $fname )) : null;
            
            $author = empty($name)   ? null : $this->html($name, 'h6');
            $author = empty($author) ? null : $this->html($author, 'div', 'name');

            $desc   = empty($guid) ? null : $this->html($guid->summary, 'div', 'summary');
            $desc   = empty($desc) ? ''   : $this->html($desc, 'div', 'desc');
            
            $bd_bar_list[] = array( 'content' => $this->html('arrow', 'div', 'arrow') . $this->html($logo . $author. $desc, 'div', 'panelist-bar clearfix'));
        }

        if($item_div !==''){ $bd_list[] = array('content'=>$item_div); }
        return $this->module( 
                /* header */ $this->html($header,'h5') . $this->html(' ', 'div', 'yom-panelist-page'),
                /* body   */ $this->html($prev_button  . $this->html($this->html($this->itemsList($bd_list), 'div', 'inner-thumb-list yui3-scrollview-loading', '', 'id="scrollview-content"'), 'div', 'outer-thumb-list') . $next_button, 'div', 'list loading' ) . $this->html($this->itemsList($bd_bar_list), 'div', 'data'),
                /* footer */ '', 
                /* class  */ 'yom-panelists', 
                /* id     */ $this->getFirstArgument('mod_id')
                );
    }
}

