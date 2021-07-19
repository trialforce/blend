<?php

namespace Filter;

/**
 * Class that manage the search SAVE list
 * Can be extend to save to database
 */
class SavedList
{

    /**
     * Return saved list file
     *
     * @return \Disk\File
     */
    public function getFile()
    {
        return new \Disk\File(\Disk\Media::getMediaPath() . 'savedlist.json');
    }

    /**
     *
     * @return stdClass
     */
    public function getObject()
    {
        $file = $this->getFile();
        $folder = $file->getFolder();

        if ($folder->isWritable() && !$file->exists())
        {
            $file->save('');
        }

        if (!$file->exists())
        {
            throw new \Exception('Impossível criar arquivo de pesquisas verifique permissão no arquivo ' . $file->getPath() . ' ');
        }

        $file->load();
        $list = json_decode($file->getContent());

        //avoid empty object
        if (!$list instanceof \stdClass)
        {
            $list = new \stdClass();
        }

        return $list;
    }

    /**
     *
     * @return array of  \View\Option
     */
    public function getOptions($pageUrl)
    {
        $json = $this->getObject();
        $list = NULL;

        if (is_object($json) && count($json) > 0)
        {
            foreach ($json as $id => $item)
            {
                if ($item->page == $pageUrl)
                {
                    $list[] = $option = new \View\Option($id, $item->title);
                    $option->setData('url', $item->page . '/?' . $item->url . '&savedList=' . $id);
                }
            }
        }

        return $list;
    }

    public function save($pageUrl, $url, $title)
    {
        if (!$title || !$url)
        {
            throw new \UserException('É necessário informar um título e url!');
        }

        $file = $this->getFile();
        $list = $this->getObject();

        $id = \Type\Text::get($title)->toFile() . '';

        $saveList = new \stdClass();
        $saveList->title = $title;
        $saveList->url = $url;
        $saveList->page = $pageUrl;
        $saveList->id = $id;

        $list->$id = $saveList;

        if (defined('JSON_PRETTY_PRINT'))
        {
            $json = json_encode($list, JSON_PRETTY_PRINT);
        }
        else
        {
            $json = json_encode($list);
        }

        if ($file->isWritable())
        {
            $file->save($json);
            return $saveList;
        }
        else
        {
            throw new \Exception('Impossível salvar pesquisa verifique permissão no arquivo ' . $file->getPath() . ' ');
        }
    }

    public function delete($id)
    {
        $file = $this->getFile();
        $list = $this->getObject();

        unset($list->$id);

        if (defined('JSON_PRETTY_PRINT'))
        {
            $json = json_encode($list, JSON_PRETTY_PRINT);
        }
        else
        {
            $json = json_encode($list);
        }

        if ($file->isWritable())
        {
            $file->save($json);
            return true;
        }
        else
        {
            throw new \Exception('Impossível remover pesquisa verifique permissão!');
        }
    }

    /**
     * Mount a url to be saved in filter  list
     *
     * @return string
     */
    public static function filtertPost()
    {
        $parts = $_REQUEST;
        unset($parts['p']);
        unset($parts['e']);
        unset($parts['v']);
        unset($parts['selectFilters']);
        unset($parts['selectGroups']);
        unset($parts['_']);
        unset($parts['paginationLimit']);
        unset($parts['savedList']);
        unset($parts['saveList']);
        unset($parts['total_notificacoes']);
        unset($parts['formChanged']);

        if (strlen($parts['q']) == 0)
        {
            unset($parts['q']);
        }

        return $parts;
    }

}
