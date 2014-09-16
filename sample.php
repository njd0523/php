<?php
require_once '/home/share/frontend/media/modules/base/renderers/MediaModulePatternRendererBase.php';
require_once '/home/share/media/models/base/MediaModelPagedListCA.php';
require_once '/home/share/frontend/media/modules/base/lib/MediaImageParse.php';

class MediaEditorPicks extends MediaModulePatternRendererBase
{
    public function render()
    {
        $html = '';
        $list = array();
        
        $mod_id  = $this->getFirstArgument('mod_id', 'mediaeditorpicks');
        $header  = $this->getFirstArgument('header');
        $hdcolor = ($this->getFirstArgument('hdcolor')) ? 'style="color:#' . $this->getFirstArgument('hdcolor') . ';"' : '';
        $bgcolor = ($this->getFirstArgument('bgcolor')) ? '#' . $this->getFirstArgument('bgcolor') : '';
        $bgimg   = ($this->getFirstArgument('bgimg')) ? 'url(' . $this->getFirstArgument('bgimg') . ')' : '';
        $bg      = ($bgcolor || $bgimg ) ? 'style="background:' . $bgimg . $bgcolor .  ';"' : '';
        $length  = $this->getFirstArgument('title_length', '20');

        for ($i=0, $j=$this->data->count(); $i<$j; $i++)
        {
            $resource = $this->data->getItem($i);

            if ($resource instanceof MediaModelStory)
            {
                $list[] = $this->generateStory($resource, $length);
            }
            else if ($resource instanceof MediaModelImage)
            {
                $list[] = $this->generatePhoto($resource, $length);
            }
            else if ($resource instanceof MediaModelPhotoGallery)
            {
                $list[] = $this->generateSlideshow($resource, $length);
            }
            else if ($resource instanceof MediaModelVideo)
            {
                $list[] = $this->generateVideo($resource, $length);
            }
            else if ($resource instanceof MediaModelLink)
            {
                $list[] = $this->generateLink($resource, $length, $i);
            }
        }

        if (count($list)>0)
        {
            $html = $this->module(
             /*hd*/ $this->html($header, 'h3', '', '', $hdcolor),
             /*bd*/ $this->itemsList($list, 'yom-list-simple','ul'),
             /*ft*/ '',
             /*class*/ 'yom-editor-picks',
             /*id*/ $mod_id,
             /*bg*/ $bg
            );
        }
        return $html;
    }
    private function generateStory($story,$length)
    {
        $item = '';

        $tags = $story->getTags();
        $is_blog = ($story->getProvider() && is_array($tags) && (array_search('ymedia:type=blogpost', array_map('strtolower',$tags))!==FALSE));
        $link = $is_blog ? $this->generateUrl(array($story,$story->getProvider()),'blog_post') : $this->generateUrl($story);

        if (count($story->getRelatedImages()) > 0 && $story->getRelatedImages()->getItem(0)->getWidth() > 0) {
            $crop  = ($story->getRelatedImages()->getItem(0)->getWidth() < $story->getRelatedImages()->getItem(0)->getHeight()) ? 'top' : 'scale';
            $photo = $story->getRelatedImages()->getItem(0);
            $media = $this->generateMoaiImage($photo,'93','57','','', $crop);
            $icon = $this->html('story', 'span');
            $head = $this->html($this->truncateTextByChar($story->getHeadline(),  $length), 'div');
            $item.= $this->link($media . $icon . $head, $link);

        }

        return $item;
    }

    private function generatePhoto($photo,$length)
    {
        $item = '';
        
        $link = $this->generateUrl($photo);
        $crop = ($photo->getWidth() < $photo->getHeight()) ? 'top' : 'scale';
        $media= $this->generateMoaiImage($photo,'93','57','','', $crop);
        $icon = $this->html('photo', 'span', 'icon-photo');
        $head = $this->html($this->truncateTextByChar($photo->getTitle(), $length), 'div');

        $item.= $this->link($media . $icon . $head, $link);

        return $item;

    }

    private function generateSlideshow($slideshow,$length)
    {
        $item = '';

        if($slideshow->getCoverPhoto())
        {
            $photo= $slideshow->getCoverPhoto();
        }
        else if(method_exists($slideshow,'getItemsList'))
        {
            $item  = $slideshow->getItemsList();
            $photo = method_exists($item,'getItem') ? $slideshow->getItemsList()->getItem(0) : '' ;
        }

        $link = !$photo ? $this->generateUrl($slideshow) : $this->generateUrl(array($photo, $slideshow));
        $crop = ($photo->getWidth() < $photo->getHeight()) ? 'top' : 'scale';
        $media= !$photo ? '' : $this->generateMoaiImage($photo,'93','57','','', $crop);
        $icon = $this->html('slideshow', 'span', 'icon-slideshow');
        $head = $this->html($this->truncateTextByChar($slideshow->getTitle(), $length), 'div');
        
        $item.= $this->link($media .$icon .$head, $link);

        return $item;
    }

    public function generateVideo($video,$length)
    {
        $item = '';

        $photo  = $video->getBestThumb();

        if($photo == '') { return; }

        $link = $this->generateUrl($video);
        $crop = ($photo->getWidth() < $photo->getHeight()) ? 'top' : 'scale';
        $media= $this->generateMoaiImage($photo,'93','57','','', $crop);
        $icon = $this->html('video', 'span', 'icon-play-small');
        $head = $this->html($this->truncateTextByChar($video->getTitle(), $length), 'div');

        $item.= $this->link($media . $icon .$head, $link);

        return $item;
    }
    private function generateLink($resource, $length, $pos = 0)
    {
        $item = '';
        $useRapid = MediaCommonUtil::useRapidTracking();

        $ults = $resource->getUltParams();
        if ($pos > 0 && !$useRapid)
        {
            $ults['pos'] = $pos;
        }

        if (count($resource->getThumbnails()) > 0 && $resource->getThumbnails()->getItem(0)->getWidth() > 0) {

            $crop  = ($resource->getThumbnails()->getItem(0)->getWidth() < $resource->getThumbnails()->getItem(0)->getHeight()) ? 'top' : 'scale';
            $photo = $resource->getThumbnails()->getItem(0);
            $media = $this->generateMoaiImage($photo,'93','57','','', $crop);
            $link = $resource->getUrl();
            $icon = $this->html('outlink', 'span');
            $head = $this->html($this->truncateTextByChar($resource->getTitle(),  $length), 'div');
            $item.= $this->link($media . $icon . $head, $link, '', $ults, array('rel'=>'nofollow','target'=>'_blank'));

        }

        return $item;
    }
    private function generateMoaiImage($image, $w, $h, $defaultUrl, $defaultAlt, $crop)
    {
        if((!$image)&&(!$defaultUrl)) {return false;}

        $imgUrl = $image ? $image->getUrl() : $defaultUrl;
        $rtnImg = $this->image(
            array(
            'url' => $this->buildImageUrl(
                 $imgUrl,
                 array('w' => $w, 'h' => $h, 'pxoff' => '0', 'pyoff' => '0', 'crop' => $crop)),
            'width'=>$w,
            'height'=>$h,
            'alt'=>($image)? $image->getTitle() : $defaultAlt
            )
        );
        return $rtnImg;
    }
    protected function buildImageUrl($url,$data)
    {
        return MediaImageParse::buildImageUrl($url,$data);
    }

}
