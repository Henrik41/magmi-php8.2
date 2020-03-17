<?php

class IdxEng extends Magmi_Engine
{
    public function engineInit($params)
    {
    }

    public function engineRun($params)
    {
    }
}

class Magmi_ReindexingPlugin extends Magmi_GeneralImportPlugin
{
    protected $_reindex;
    protected $_indexlist = null;
    protected $_mdh;
    protected $_eng;

    public function getPluginInfo()
    {
        return array("name"=>"Magmi Magento Reindexer","author"=>"Dweeves","version"=>"1.0.4",
            "url"=>$this->pluginDocUrl("Magmi_Magento_Reindexer"));
    }

    public function initIndexList()
    {
        if ($this->_eng==null) {
            $this->_eng=new IdxEng();
            $this->_eng->initialize();
            $this->_eng->connectToMagento();
        }

        $sql="SELECT indexer_id FROM ".$this->_eng->tablename('indexer_state');
        $result=$this->_eng->selectAll($sql);
        $idxlist=array();
        if (count($result)) {
            foreach ($result as $row) {
                $idxlist[]=$row["indexer_id"];
            }
            return implode(',', $idxlist);
        } else {
            return array();
        }
    }

    public function afterImport()
    {
        $this->fixFlat();
        $this->log("running indexer", "info");
        $this->updateIndexes();
        return true;
    }

    public function OptimEav()
    {
        $tables = array("catalog_product_entity_varchar","catalog_product_entity_int","catalog_product_entity_text",
            "catalog_product_entity_decimal","catalog_product_entity_datetime","catalog_product_entity_media_gallery",
            "catalog_product_entity_tier_price");

        $cpe = $this->tablename('catalog_product_entity');
        $this->log("Optmizing EAV Tables...", "info");
        foreach ($tables as $t) {
            $this->log("Optmizing $t....", "info");
            $sql = "DELETE ta.* FROM " . $this->tablename($t) . " as ta
			LEFT JOIN $cpe as cpe on cpe.entity_id=ta.entity_id
			WHERE ta.store_id=0 AND cpe.entity_id IS NULL";
            $this->delete($sql);
            $this->log("$t optimized", "info");
        }
    }

    public function fixFlat()
    {
        $this->log("Cleaning flat tables before reindex...", "info");
        $stmt = $this->exec_stmt("SHOW TABLES LIKE '" . $this->tablename('catalog_product_flat') . "%'", null, false);
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tname = $row[0];
            // removing records in flat tables that are no more linked to entries in catalog_product_entity table
            // for some reasons, this seem to happen
            $sql = "DELETE cpf.* FROM $tname as cpf
			LEFT JOIN " . $this->tablename('catalog_product_entity') . " as cpe ON cpe.entity_id=cpf.entity_id
			WHERE cpe.entity_id IS NULL";
            $this->delete($sql);
        }
    }

    public function getPluginParamNames()
    {
        return array("REINDEX:indexes","REINDEX:phpcli");
    }

    public function getIndexList()
    {
        if ($this->_indexlist==null) {
            $this->_indexlist=$this->initIndexList();
        }
        return $this->_indexlist;
    }

    public function updateIndexes()
    {
        // make sure we are not in session
        if (session_id() !== "") {
            session_write_close();
        }
        $cl = $this->getParam("REINDEX:phpcli") . " bin/magento";
        $idxlstr = $this->getParam("REINDEX:indexes", "");
        $idxlist = explode(",", $idxlstr);
        if (count($idxlist) == 0) {
            $this->log("No indexes selected , skipping reindexing...", "warning");
            return true;
        }
        foreach ($idxlist as $idx) {
            $tstart = microtime(true);
            $this->log("Reindexing $idx....", "info");

            // Execute Reindex command, and specify that it should be ran from Magento directory
            $out = $this->_mdh->exec_cmd($cl, "indexer:reindex $idx", $this->_mdh->getMagentoDir());
            $this->log($out, "info");
            $tend = microtime(true);
            $this->log("done in " . round($tend - $tstart, 2) . " secs", "info");
            if (Magmi_StateManager::getState() == "canceled") {
                exit();
            }
            flush();
        }
    }


    public function isRunnable()
    {
        return array(FSHelper::getExecMode() != null,"");
    }

    public function initialize($params)
    {
        $magdir = Magmi_Config::getInstance()->getMagentoDir();
        $this->_mdh = MagentoDirHandlerFactory::getInstance()->getHandler($magdir);
        $this->log("Using execution mode :" . $this->_mdh->getexecmode(), "startup");
    }
}
