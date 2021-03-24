<?php

class WowzaMediaServerNode extends MediaServerNode {
	const DEFAULT_MANIFEST_PORT = 1935;
	const DEFAULT_WEB_SERVICES_PORT = 888;
	const DEFAULT_WEB_SERVICES_PROTOCOL = 'http';
	const DEFAULT_TRANSCODER = 'default';
	const DEFAULT_GPUID = -1;
	
	const CUSTOM_DATA_TRANSCODER_CONFIG = 'transcoder';
	const CUSTOM_DATA_GPUID = 'gpuid';
	const CUSTOM_DATA_LIVE_SERVICE_PORT = 'live_service_port';
	const CUSTOM_DATA_LIVE_SERVICE_PROTOCOL = 'live_service_protocol';
	const CUSTOM_DATA_LIVE_SERVICE_INTERNAL_DOMAIN = 'live_service_internal_domain';

	const RECORDING_LIVE_TYPE_URL = '/recording/';
	const CLIPPING_LIVE_TYPE_URL = '/clip/';
	const WEB_SERVICE_LIVE = 'live';
	
	static protected $webServices = array(
		self::WEB_SERVICE_LIVE => 'KalturaMediaServerLiveService',
	);
	
	/**
	 * Applies default values to this object.
	 * This method should be called from the object's constructor (or equivalent initialization method).
	 * @see __construct()
	 */
	public function applyDefaultValues()
	{
		parent::applyDefaultValues();
		
		$this->setType(WowzaPlugin::getWowzaMediaServerTypeCoreValue(WowzaMediaServerNodeType::WOWZA_MEDIA_SERVER));
	}
	
	/**
	 * @param string $serviceName
	 * @return KalturaMediaServerClient
	 */
	public function getWebService($serviceName)
	{
		if(!isset(self::$webServices[$serviceName]))
			return null;
		
		$serviceClass = self::$webServices[$serviceName];
		
		$domain = $this->getLiveServiceInternalDomain() ? $this->getLiveServiceInternalDomain() : $this->getHostname();
		$port = $this->getLiveServicePort();
		$protocol = $this->getLiveServiceProtocol();
		
		$url = "$protocol://$domain:$port/$serviceName?wsdl";
		KalturaLog::debug("Service URL: $url");
		return new $serviceClass($url);
	}
	
	public function getLiveWebServiceName()
	{
		return WowzaMediaServerNode::WEB_SERVICE_LIVE;
	}
	
	public function getManifestUrl($protocol = 'http', $format = null)
	{		
		$playbackHost = $this->getPlaybackHost($protocol, $format);
		
		$hostname = $this->getHostname();
		if(!$this->getIsExternalMediaServer())
			$hostname = preg_replace('/\..*$/', '', $hostname);
		
		$url = "$protocol://$playbackHost";
		$url = str_replace("{hostName}", $hostname, $url);
		return $url;
	}
	
	public function getPlaybackHost($protocol = 'http', $format = null, $baseUrl = null, $deliveryType = null)
	{
		$hostname = $this->getHostname();
		if(!$this->getIsExternalMediaServer())
			$hostname = preg_replace('/\..*$/', '', $hostname);
		
		$mediaServerConfig = kConf::getMap('media_servers');
		if($baseUrl && $baseUrl !== '')
		{
			$domain = preg_replace("(https?://)", "", $baseUrl);
			$domain = rtrim($domain, "/");
		}
		else
		{
			$domain = $this->getDomainByProtocolAndFormat($mediaServerConfig, $protocol, $format);
			$port = $this->getPortByProtocolAndFormat($mediaServerConfig, $protocol, $format);
			$domain = "$domain:$port";
		}
		
		$playbackHost = "$protocol://$domain/";
		$playbackHost = str_replace("{hostName}", $hostname, $playbackHost);
		return $playbackHost;
	}
	
	public function getAppNameAndPrefix()
	{
		$appNameAndPrefix = '';
		
		$hostname = $this->getHostname();
		if(!$this->getIsExternalMediaServer())
			$hostname = preg_replace('/\..*$/', '', $hostname);
		
		$mediaServerConfig = kConf::getMap('media_servers');
		$appPrefix = $this->getApplicationPrefix($mediaServerConfig);
		$applicationName = $this->getApplicationName();
		
		if($appPrefix && $appPrefix !== '')
			$appNameAndPrefix .= rtrim($appPrefix, "/") . "/";
		$appNameAndPrefix .= "$applicationName";
		
		$appNameAndPrefix = str_replace("{hostName}", $hostname, $appNameAndPrefix);
		
		return $appNameAndPrefix;
	}

	public function getEnvDc()
	{
		return 'dc-' . $this->getDc();
	}

    public static function getSegmentDurationUrlString($sd)
    {
        return "sd/$sd/";
    }

	protected function getDomainByProtocolAndFormat($mediaServerConfig, $protocol = 'http', $format = null)
	{
		$domain = $this->getPlaybackDomain();
		$domainField = "domain" . ($format ? "-$format" : "");
		$domain = $this->getValueByField($mediaServerConfig, $domainField, $domain);
		
		$mediaServerPlaybackDomainConfig = $this->getMediaServerPlaybackDomainConfig();
		if($mediaServerPlaybackDomainConfig)
		{
			$domainField = $protocol . ($format ? "-$format" : "");
			if(isset($mediaServerPlaybackDomainConfig[$domainField]))
				$domain = $mediaServerPlaybackDomainConfig[$domainField];
		}
		
		return $domain;
	}

	protected function getPortByProtocolAndFormat($mediaServerConfig, $protocol = 'http', $format = null)
	{
		$port = WowzaMediaServerNode::DEFAULT_MANIFEST_PORT;
		$portField = 'port' . ($protocol != 'http' ? "-$protocol" : "") . ($format ? "-$format" : "");
		$port = $this->getValueByField($mediaServerConfig, $portField, $port);
		
		$mediaServerPortConfig = $this->getMediaServerPortConfig();
		if($mediaServerPortConfig)
		{
			$portField = $protocol . ($format ? "-$format" : "");
			if(isset($mediaServerPortConfig[$portField]) && $mediaServerPortConfig[$portField] !== WowzaMediaServerNode::DEFAULT_MANIFEST_PORT)
				$port = $mediaServerPortConfig[$portField];
		}
		
		return $port;
	}

	protected function getApplicationPrefix($mediaServerConfig)
	{
		$appPrefix = "";
		$appPrefix = $this->getValueByField($mediaServerConfig, 'appPrefix', $appPrefix);
		
		if(!is_null($this->getAppPrefix()))
			$appPrefix = $this->getAppPrefix();
		
		return $appPrefix;
	}
	
	protected function getValueByField($config, $filedValue, $defaultValue)
	{
		$value = $defaultValue;
		
		if(isset($config[$filedValue]))
			$value = $config[$filedValue];
		if(isset($config['dc-'.$this->getDc()][$filedValue]))
			$value = $config['dc-'.$this->getDc()][$filedValue];
		if(isset($config[$this->getHostname()][$filedValue]))
			$value = $config[$this->getHostname()][$filedValue];
		
		return $value;
	}
	
	public function setTranscoder($transcoder)
	{
		$this->putInCustomData(self::CUSTOM_DATA_TRANSCODER_CONFIG, $transcoder);
	}
	
	public function getTranscoder()
	{
		return $this->getFromCustomData(self::CUSTOM_DATA_TRANSCODER_CONFIG, null, WowzaMediaServerNode::DEFAULT_TRANSCODER);
	}
	
	public function setGPUID($gpuid)
	{
		$this->putInCustomData(self::CUSTOM_DATA_GPUID, $gpuid);
	}
	
	public function getGPUID()
	{
		return $this->getFromCustomData(self::CUSTOM_DATA_GPUID, null, WowzaMediaServerNode::DEFAULT_GPUID);
	}
	
	public function setLiveServicePort($liveServicePort)
	{
		$this->putInCustomData(self::CUSTOM_DATA_LIVE_SERVICE_PORT, $liveServicePort);
	}
	
	public function getLiveServicePort()
	{
		return $this->getFromCustomData(self::CUSTOM_DATA_LIVE_SERVICE_PORT, null, WowzaMediaServerNode::DEFAULT_WEB_SERVICES_PORT);
	}
	
	public function setLiveServiceProtocol($liveServiceProtocol)
	{
		$this->putInCustomData(self::CUSTOM_DATA_LIVE_SERVICE_PROTOCOL, $liveServiceProtocol);
	}
	
	public function getLiveServiceProtocol()
	{
		return $this->getFromCustomData(self::CUSTOM_DATA_LIVE_SERVICE_PROTOCOL, null, WowzaMediaServerNode::DEFAULT_WEB_SERVICES_PROTOCOL);
	}
	
	public function setLiveServiceInternalDomain($liveServiceInternalDomain)
	{
		$this->putInCustomData(self::CUSTOM_DATA_LIVE_SERVICE_INTERNAL_DOMAIN, $liveServiceInternalDomain);
	}
	
	public function getLiveServiceInternalDomain()
	{
		return $this->getFromCustomData(self::CUSTOM_DATA_LIVE_SERVICE_INTERNAL_DOMAIN, null, null);
	}

	public static function getEntryIdUrl(DeliveryProfileDynamicAttributes $da)
	{
		if ($da->getServeVodFromLive())
		{
			$entryId = $da->getServeLiveAsVodEntryId();
			return '/' . self::ENTRY_ID_URL_PARAM . "/$entryId/";
		}
		return parent::getEntryIdUrl($da);
	}

	public static function modifyUrlForVodFromLive($liveUrl, DeliveryProfileDynamicAttributes $da)
	{
		$entryId = $da->getServeLiveAsVodEntryId();
		$entry = entryPeer::retrieveByPK($entryId);

		$liveType = self::RECORDING_LIVE_TYPE_URL;
		if ($entry && $entry->getFlowType() == EntryFlowType::LIVE_CLIPPING)
		{
			$liveType = self::CLIPPING_LIVE_TYPE_URL;
		}
		$liveUrl = str_replace('/live/', $liveType, $liveUrl);
		return $liveUrl;
	}

} // WowzaMediaServer
