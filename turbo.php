<?php

header('Content-Type: text/html; charset=utf-8');

// Configuration block

$siteurl="https://example.com"; 
$title = "Название канала";
$description = "Краткое описание канала";
$author = "Иван Иванов";
// Данные для шапки
$logo = "/images/Logo/logotip.png"; 
$menutype = "mainmenu"; //Тип меню
//define( 'TURBO_HEADER', true );
/* Конец настроек шапки */
// Configuration block end 

define( '_JEXEC', 1 );
if ( file_exists( __DIR__ . '/defines.php' ) ) {
    include_once __DIR__ . '/defines.php';
}
if ( !defined( '_JDEFINES' ) ) {
    define( 'JPATH_BASE', __DIR__ );
    require_once JPATH_BASE . '/includes/defines.php';
}
require_once JPATH_BASE . '/includes/framework.php';
$app = JFactory::getApplication('site');


/* Выборка с базы данных для материалов из контента */
$db = JFactory::getDbo();
$query = $db->getQuery( true )
    ->select($db->quoteName('id'))
    ->select($db->quoteName('catid'))
    ->select($db->quoteName('title'))
    ->select($db->quoteName('publish_up'))
    ->select($db->quoteName('introtext'))
    ->select($db->quoteName('fulltext'))
    ->from( '#__content' )
    ->where('state=1') // Только опубликованные материалы
    ->where('access=1'); // Доступные для всех
$list = $db->setQuery( $query )->loadObjectList();

/* Выборка с базы данных для материалов из модулей */
$queryMod = $db->getQuery( true )
    ->select($db->quoteName('title'))
    ->select($db->quoteName('content'))
    ->select($db->quoteName('params'))
    ->select($db->quoteName('id'))
    ->from( '#__modules' )
    ->where('id=149 or id=155 or id=139 or id=156 or id=157 or id=158 or id=159');
$listMod = $db->setQuery( $queryMod )->loadObjectList();

/* Выборка с базы данных для Главной */
$queryMain = $db->getQuery( true )
    ->select($db->quoteName('title'))
    ->select($db->quoteName('content'))
    ->select($db->quoteName('id'))
    ->from( '#__modules' )
    ->where('id=124 or id=123');
$listMain = $db->setQuery( $queryMain )->loadObjectList();

/*if ( defined( 'TURBO_HEADER' ) ) {
    $query = $db->getQuery( true )
        ->select($db->quoteName('id'))
        ->select($db->quoteName('title'))
        ->select($db->quoteName('link'))
        ->from( '#__menu' )
        ->where('published=1') // Только опубликованные
        ->where('access=1') // Доступные для всех
        ->where("menutype='$menutype'"); // Задаём тип меню
    $menu = $db->setQuery( $query,0,9 )->loadObjectList();
}*/

/* Начало файла */
$xml='<?xml version="1.0" encoding="utf-8"?>
<rss
    xmlns:yandex="http://news.yandex.ru"
    xmlns:media="http://search.yahoo.com/mrss/"
    xmlns:turbo="http://turbo.yandex.ru"
    version="2.0">
	<channel>
		<title>'.$title.'</title>
		<description><![CDATA['.$description.']]></description>
		<link>'.$siteurl.'/</link>
		<lastBuildDate>'.date(DATE_ATOM).'</lastBuildDate>
		<language>ru</language>';

/* Главная */
$xml.='<item turbo="true">
<title>Главная страница</title>
<link>https://example.com</link>
<turbo:content><![CDATA[';
foreach($listMain as $item) {
    $content = htmlspecialchars_decode(str_ireplace('src="images','src="'.$siteurl.'/images',$item->content));
    $content = str_ireplace('href="#','href="'.$link.'/#',$content);
    $content = str_ireplace('src="/','src="'.$siteurl.'/',$content);
    $xml.= $content;
}
$xml.='<div data-block="share" data-network="vkontakte, twitter, facebook, google, telegram, odnoklassniki"></div>';
$xml.=']]></turbo:content>
            <author>'.$author.'</author>
            <pubDate>'.$item->publish_up.'</pubDate>
        </item>';

/* Контент из модулей */
foreach($listMod as $item) {
	    $link = $siteurl;
//для не ЧПУ
	    switch ($item->id) {
	         case '149': 
	         $link .= '/index.php/ru/services';
	         break;
	         case '155': 
	         $link .= '/index.php/ru/international-shipping';
	         break;
	         case '139': 
	         $link .= '/index.php/ru/about/about-us';
	         break;
	         case '156': 
	         $link .= '/index.php/ru/international-shipping/zheleznodorozhnye';
	         break;
	         case '157': 
	         $link .= '/index.php/ru/international-shipping/avio';
	         break;
	         case '158': 
	         $link .= '/index.php/ru/international-shipping/marine';
	         break;
	         case '159': 
	         $link .= '/index.php/ru/international-shipping/multimodal-transportation';
	         break;
	         /*case '160': 
	         $link .= '/index.php/ru/international-shipping';
	         break;*/
	    }
	
    
    $content = htmlspecialchars_decode(str_ireplace('src="images','src="'.$siteurl.'/images',$item->content));
    $content = str_ireplace('href="#','href="'.$link.'/#',$content);
    $content = str_ireplace('src="/','src="'.$siteurl.'/',$content);
 
    $xml.='
            <item turbo="true">
            <title>'.$item->title.'</title>
            <link>'.$link.'</link>';
    $xml.='<turbo:content><![CDATA[';
    if ( defined( 'TURBO_HEADER' ) ) {
        $xml.='<header>
                       <figure>
                           <img
                            src="'.$siteurl.$logo.'" />
                       </figure>
                       <h1>'.$title.'</h1>
                       <h2>'.$description.'</h2>
                       <menu>';
        foreach($menu as $menuitem){
            $xml.='<a href="'.$siteurl.\Joomla\CMS\Router\Route::_($menuitem->link).'">
                                '.htmlspecialchars($menuitem->title).'
                           </a>';
        }
        $xml.='</menu>
                </header>';
    }
    $xml.=$content;
    preg_match_all('#module_type\\\":\\\"module\\\",\\\"id\\\":\\\"(\d+)\\\"#', $item->params,  $matches);
    foreach ($matches[1] as $elem) {
        $queryElem = $db->getQuery( true )
        ->select($db->quoteName('title'))
        ->select($db->quoteName('content'))
        ->from( '#__modules' )
        ->where('id='.$elem.'');
        $listElem = $db->setQuery( $queryElem )->loadObjectList();
        foreach ($listElem as $block) {
            $contentElem = htmlspecialchars_decode(str_ireplace('src="images','src="'.$siteurl.'/images',$block->content));
            $contentElem = str_ireplace('href="#','href="'.$link.'/#',$contentElem);
            $contentElem = str_ireplace('src="/','src="'.$siteurl.'/',$contentElem);
            $xml.=$contentElem;
        }
    }    
    $xml.='<div data-block="share" data-network="vkontakte, twitter, facebook, google, telegram, odnoklassniki"></div>'; // Добавляем кнопки поделиться в соцсети
    if(!empty($comments)) {
        $xml.='<div data-block="comments" data-url="'.$link.'#addcomments">';
        foreach ($comments as $comment) {
            $xml .= '<div
                data-block="comment"
                data-author="' . $comment->name . '" 
                data-subtitle="' . $comment->date . '"
               >
                   <div data-block="content">
                       <p>
                            ' . $comment->comment . '
                       </p>
                   </div> 
               </div>';
        }
        $xml.='</div>';
    }
    $xml.=']]></turbo:content>
            <author>'.$author.'</author>
            <pubDate>'.$item->publish_up.'</pubDate>
        </item>';
}

/* Контент из контента */
foreach($list as $item) {

    $link = $siteurl.\Joomla\CMS\Router\Route::_('index.php?option=com_content&view=article&id='.$item->id.'&catid='.$item->catid);
    $introtext = htmlspecialchars_decode(str_ireplace('src="images','src="'.$siteurl.'/images',$item->introtext));
    $introtext = str_ireplace('href="#','href="'.$link.'/#',$introtext);
    $introtext = str_ireplace('src="/','src="'.$siteurl.'/',$introtext);
    $content = $introtext;
    if(!empty($item->fulltext)){
        $fulltext = htmlspecialchars_decode(str_ireplace('src="images','src="'.$siteurl.'/images',$item->fulltext));
        $fulltext = str_ireplace('href="#','href="'.$link.'/#',$fulltext);
        $fulltext = str_ireplace('src="/','src="'.$siteurl.'/',$fulltext);
        $content .= $fulltext;
    }
    $xml.='
			<item turbo="true">
			<title>'.$item->title.'</title>
			<link>'.$link.'</link>';
    $xml.='<turbo:content><![CDATA[';
    if ( defined( 'TURBO_HEADER' ) ) {
        $xml.='<header>
                       <figure>
                           <img
                            src="'.$siteurl.$logo.'" />
                       </figure>
                       <h1>'.$title.'</h1>
                       <h2>'.$description.'</h2>
                       <menu>';
        foreach($menu as $menuitem){
            $xml.='<a href="'.$siteurl.\Joomla\CMS\Router\Route::_($menuitem->link).'">
								'.htmlspecialchars($menuitem->title).'
						   </a>';
        }
        $xml.='</menu>
                </header>';
    }
    $xml.=$content;
    $xml.='<div data-block="share" data-network="vkontakte, twitter, facebook, google, telegram, odnoklassniki"></div>'; // Добавляем кнопки поделиться в соцсети
    if(!empty($comments)) {
        $xml.='<div data-block="comments" data-url="'.$link.'#addcomments">';
        foreach ($comments as $comment) {
            $xml .= '<div
                data-block="comment"
                data-author="' . $comment->name . '" 
                data-subtitle="' . $comment->date . '"
               >
                   <div data-block="content">
                       <p>
                            ' . $comment->comment . '
                       </p>
                   </div> 
               </div>';
        }
        $xml.='</div>';
    }
    $xml.=']]></turbo:content>
			<author>'.$author.'</author>
			<pubDate>'.$item->publish_up.'</pubDate>
		</item>';
}
$xml.='</channel>
</rss>';
/* Исключаем лишние символы, заменяем на нужные теги */
$xml = preg_replace('#images\\\gofast\\\icon\\\#', 'images/gofast/icon/', $xml);
$xml = preg_replace('#images\\\gofast\\\text-image\\\wagons\\\#', 'images/gofast/text-image/wagons/', $xml);
$xml = preg_replace('#\[yt_gallery caption.*?\]#', '<div data-block="gallery">', $xml);
$xml = preg_replace('#\[yt_gallery_item tag=\"\" yt_title="Title gallery\" #', '<img ', $xml);
$xml = preg_replace('#\[/yt_gallery_item\]#', '\/>', $xml);
$xml = preg_replace('#\[/yt_gallery\]#', '</div>', $xml);
$xml = preg_replace('# video_addr=\"\" \]#', '', $xml);
$xml = preg_replace('#\[yt_icon.+?\]#', '&bull;', $xml);
$xml = preg_replace('#\[yt_divider.+?\]#', '', $xml);

/* Генерируем файл */
if (file_put_contents($_SERVER['DOCUMENT_ROOT'].'/turbo.xml', $xml))
{
    echo "XML файл сгенерирован";
}
else
{
    echo "Неизвестная ошибка";
}
?>