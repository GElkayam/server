<?php
/**
 * @package Core
 * @subpackage model.data
 */
class kStorageExportJobData extends kStorageJobData
{
	/**
	 * @var bool
	 */   	
    private $force; 
    
    /**
	 * @var bool
	 */
    private $createLink;

	/**
	 * @var string
	 */
	private $assetId;

	/**
	 * @var string
	 */
	private $externalUrl;

	public static function getInstance($protocol)
	{
		$data = null;
		switch($protocol)
		{
			case StorageProfile::STORAGE_PROTOCOL_S3:
				$data = new kAmazonS3StorageExportJobData();
				break;
			default:
				$data = KalturaPluginManager::loadObject('kStorageExportJobData', $protocol);
				break;
		}
		if (!$data)
			$data = new kStorageExportJobData();
		
		return $data;
	}
	
	public function setStorageExportJobData(StorageProfile $externalStorage, FileSync $fileSync, FileSync $srcFileSync, $force = false)
	{
		$this->setServerUrl($externalStorage->getStorageUrl()); 
	    $this->setServerUsername($externalStorage->getStorageUsername()); 
	    $this->setServerPassword($externalStorage->getStoragePassword());
	    $this->setServerPrivateKey($externalStorage->getPrivateKey());
	    $this->setServerPublicKey($externalStorage->getPublicKey());
	    $this->setServerPassPhrase($externalStorage->getPassPhrase());
	    $this->setFtpPassiveMode($externalStorage->getStorageFtpPassiveMode());
	    $this->setSrcFileSyncLocalPath($srcFileSync->getFullPath());
		$this->setSrcFileEncryptionKey($srcFileSync->getEncryptionKey());
		$this->setSrcFileSyncId($fileSync->getId());
		$this->setForce($force);
		$this->setDestFileSyncStoredPath($externalStorage->getStorageBaseDir() . '/' . $fileSync->getFilePath());
		$this->setCreateLink($externalStorage->getCreateFileLink());

		if($srcFileSync->getFileType() == FileSync::FILE_SYNC_FILE_TYPE_URL)
		{
			$assetId = $srcFileSync->getObjectId();
			$asset = assetPeer::retrieveById($assetId);

			$this->setAssetId($assetId);
			$this->setExternalUrl($srcFileSync->getExternalUrl($asset->getEntryId()));
		}
	}
	
	function calculateEstimatedEffort(BatchJob $batchJob) {
		$fileSize = filesize($this->getSrcFileSyncLocalPath());
		if($fileSize !== False)
			return $fileSize;
		
		return self::MAX_ESTIMATED_EFFORT;
	}
        
	/**
	 * @return the $force
	 */
	public function getForce()
	{
		return $this->force;
	}

	/**
	 * @param $force the $force to set
	 */
	public function setForce($force)
	{
		$this->force = $force;
	}
	
	/**
	 * @return the $createLink
	 */
	public function getCreateLink()
	{
		return $this->createLink;
	}

	/**
	 * @param createLink the $createLink to set
	 */
	public function setCreateLink($createLink)
	{
		$this->createLink = $createLink;
	}

	/**
	 * @return the $assetId
	 */
	public function getAssetId()
	{
		return $this->assetId;
	}

	/**
	 * @param assetId the $assetId to set
	 */
	public function setAssetId($assetId)
	{
		$this->assetId = $assetId;
	}

	/**
	 * @return the $externalUrl
	 */
	public function getExternalUrl()
	{
		return $this->externalUrl;
	}

	/**
	 * @param externalUrl the $externalUrl to set
	 */
	public function setExternalUrl($externalUrl)
	{
		$this->externalUrl = $externalUrl;
	}

}
