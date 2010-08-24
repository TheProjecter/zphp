<?php 

function smarty_block_bracket($params, $content, &$smarty, &$repeat)
{
    // only output on the closing tag
    if(!$repeat)
    {
        if (isset($content)) 
        {
            // do some intelligent translation thing here with $content
            return '{' . $content . '}';
        }
    }
}