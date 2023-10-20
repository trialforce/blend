<?php

namespace DataHandle;

/**
 * Class that parses information about browser.
 * Based on user agent.
 *
 * Examples (Os, browser, version):
 * Window chrome 32 - Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.102 Safari/537.36
 *
 */
class UserAgent
{

    /**
     * Platform Windows
     */
    const PLATFORM_WINDOWS = 'windows';

    /**
     * Platform Android
     */
    const PLATFORM_ANDROID = 'android';

    /**
     * Plataform iphone
     */
    const PLATFORM_IPHONE = 'iphone';

    /**
     * Plataform windows phone
     */
    const PLATFORM_WINDOWPHONE = 'windowsphone';

    /* Platform black berry */
    const PLATFORM_BLACKBERRY = 'blackberry';

    /**
     * Platform Linux
     */
    const PLATFORM_LINUX = 'linux';

    /**
     * Platform Mac
     */
    const PLATFORM_MAC = 'mac';

    /**
     * Platform Robot
     */
    const PLATFORM_BOT = 'bot';

    /**
     * Platform service, php, java, indy
     */
    const PLATFORM_SERVICE = 'service';

    /**
     * Browser Microsoft Internet Explorer
     */
    const BROWSER_IE = 'ie';

    /**
     * Browser Microsoft Edge
     */
    const BROWSER_EDGE = 'edge';

    /**
     * Browser Mozilla Firefox
     */
    const BROWSER_FIREFOX = 'firefox';

    /**
     * Browser Google Chrome
     */
    const BROWSER_CHROME = 'chrome';

    /**
     * Browser Opera
     */
    const BROWSER_OPERA = 'opera';

    /**
     * Browser Apple Safari
     */
    const BROWSER_SAFARI = 'safari';

    /**
     * Browser Linux Linx (text browser)
     */
    const BROWSER_LYNX = 'Lynx';

    /**
     * Browser android
     */
    const BROWSER_ANDROID = 'android';

    /**
     * Browser Netscape
     */
    const BROWSER_NETSCAPE = 'netscape';

    /**
     * Browser Unknown
     */
    const BROWSER_UNKNOWN = '?';

    /**
     * Developer Microsoft
     */
    const DEVELOPER_MICROSOFT = 'Microsoft';

    /**
     * Developer Google
     */
    const DEVELOPER_GOOGLE = 'Google';

    /**
     * Developer facebook
     */
    const DEVELOPER_FACEBOOK = 'Facebook';

    /**
     * Developer w3c
     */
    const DEVELOPER_W3C = 'w3c';

    /**
     * Developer Apple
     */
    const DEVELOPER_APPLE = 'Apple';

    /**
     * Developer Mozila
     */
    const DEVELOPER_MOZILLA = 'Mozilla';

    /**
     * Developer Opera
     */
    const DEVELOPER_OPERA = 'Opera';

    /**
     * User agent
     * @var string
     */
    protected $userAgent = UserAgent::BROWSER_UNKNOWN;

    /**
     * Platform
     *
     * @var string
     */
    protected $platform = UserAgent::BROWSER_UNKNOWN;

    /**
     * Complete name
     *
     * @var string
     */
    protected $completeName = UserAgent::BROWSER_UNKNOWN;

    /**
     * Browser name
     *
     * @var string
     */
    protected $name = UserAgent::BROWSER_UNKNOWN;

    /**
     * Ub - used to detect version
     *
     * @var string
     */
    protected $ubname = UserAgent::BROWSER_UNKNOWN;

    /**
     * Version
     *
     * @var string
     */
    protected $version = UserAgent::BROWSER_UNKNOWN;

    /**
     * Simple version
     * @var string
     */
    protected $simpleVersion = UserAgent::BROWSER_UNKNOWN;

    /**
     * If is mobile
     *
     * @var boolean
     */
    protected $mobile = false;

    /**
     * Browser developer
     *
     * @var developer
     */
    protected $developer = UserAgent::BROWSER_UNKNOWN;

    /**
     * Service list (user requested bots)
     * @var array
     */
    public static $serviceList = array(
        'Google_Analytics_Snippet_Validator', //Google analycts
        'Google-Ads-Creatives-Assistant', //Google Ads
        'Google Page Speed Insights', //Google pagespeed
        'Google-Site-Verification', //google site verification, developer tools
        'Chrome Privacy Preserving Prefetch Proxy', //google privacy .well-known/traffic-advice
        'Google-AdWords-Express', //googe adworss
        'Google-Adwords-Instant', //google adwords
        'Google-Structured-Data-Testing-Tool', //google adwords
        'Chrome-Lighthouse', //google adwords
        'Google-speakr', //google adwords
        'W3C_Validator', //W3c Validator
        'DowntimeDetector', //http://downforeveryoneorjustme.com
        'facebook', //http://facebook.com
        'semrush', //Sem Rush
        'SiteAuditBot', //semrush
        'WhatsApp', //WhatsApp Share??
        'Twitterbot', //twitter
        'Pinterest', //http://www.pinterest.com
        'SkypeUriPreview', //skype uri preview
        'Wget', //wget from linux
        'Typhoeus', //libcurl wrapper for ruby
        'Ruby', //ruby default user agent
        'Blackboard Safeassign', //prevent plagiarism
        'curl',
        'RukiCrawler',
        'Apache-HttpClient',
        'validator.w3.org',
        'DuckDuckGo', //search engine
        'Microsoft Windows Network Diagnostics',
        'Microsoft Office Protocol Discovery',
        'Microsoft-WebDAV-MiniRedir',
        'Yahoo Link Preview',
        'Yahoo Ad monitoring'
    );

    /**
     * Bot list
     * @var array
     */
    public static $botList = array(
        'AhrefsBot',
        'ltx71',
        'WebIndex',
        'Googlebot',
        'Discordbot',
        'python', //python default user agent
        'php', //php default user agent
        'Go-http-client', //go default user agent (google)
        'dotbot',
        'bingbot',
        'VB Project',
        'spbot',
        'MJ12bot',
        'msnbot',
        'Yahoo! Slurp',
        'YandexBot',
        'Exabot',
        'linkdexbot',
        'AdsBot-Google', //Google Adwords
        'BingPreview',
        'Mechanize', //Mechanize https://github.com/sparklemotion/mechanize
        'Mediapartners-Google', //Google Partners????
        'SiteExplorer', //http://siteexplorer.info BackLink detector
        'roboto', //show in robots list
        'WinHttp', //c# default useragent
        'wotbox', // generic bot
        'NetcraftSurveyAgent', //generic bot
        'okhttp', //generic bot
        'DomainAppender', //http://www.profound.net/domainappender
        'safedns', //www.safedns.com
        'datagnionbot', //http://www.datagnion.com/bot.htm
        'ips-agent', //Verisgin bot http://pieroxy.net/user-agent/db.html?id=BlackBerry9000%2F4.6.0.167+Profile%2FMIDP-2.0+Configuration%2FCLDC-1.1+VendorID%2F102+ips-agent&query=BlackBerry
        'Synapse',
        'Whibse',
        'CCBot', //http://commoncrawl.org/faq/
        'WeCrawlForThePeace',
        'Dataprovider', //https://www.dataprovider.com
        'Uptimebot', //http://www.uptime.com/uptimebot
        'www.ru', //www.ru strange russian bot
        'TwengaBot', //TwengaBot http://www.twenga.com/bot.html e-commerce webcrawler
        'rogerbot', //https://moz.com/help/guides/moz-procedures/what-is-rogerbot
        'Pulsepoint', //marketing platform //http://www.pulsepoint.com/
        'CheckMarkNetwork', //http://www.checkmarknetwork.com/ brand protection
        'CRAZYWEBCRAWLER', //custom webcrawler network
        'Dispatch', //don't know
        'KickFire', //news bot
        'archive.org_bot', //web archive
        'Leikibot', //don't know seen germany
        'oBot',
        'MixrankBot',
        'netseer',
        'BLEXBot',
        'MegaIndex',
        'JDatabaseDriverMysqli', //?????
        'linkfluence', //franch bot
        'Cpanel',
        'LinkedInBot',
        'IAS crawler',
        'libwww-perl', //perl language
        'package http', //Go 1.1 package http
        'ICAP-IOD',
        'Plukkie',
        'domaincrawler',
        'Sitemaps Generator',
        'YandexImages',
        'Findxbot',
        'WBSearchBot',
        'TeeRaidBot',
        'zgrab',
        'WebFuck',
        'AnyEvent',
        'Netcraft',
        'memoryBot',
        'Jakarta',
        'bitlybot',
        'SurdotlyBot',
        'TweetmemeBot',
        'SMTBot',
        'GarlikCrawler',
        'VelenPublicWebCrawler',
        'ia_archiver',
        'Grammarly',
        'unfurlist',
        'Scrapy',
        'Java',
        'ExtLinksBot',
        'dcrawl',
        'ShortLinkTranslate',
        'DnyzBot',
        'crawler4j ',
        'Genieo',
        'Codewisebot',
        'BUbiNG',
        'Traackr.com',
        'SetCronJob',
        'WhatCMSBot',
        'Barkrowler',
        'MauiBot',
        'Jetty',
        'Sideqik',
        'Telesphoreo',
        'ZmEu',
        'Jersey',
        'VoluumDSP',
        'Microsoft Office Word 2013',
        'Cliqzbot',
        'SEC test agent',
        'Atomic_Lead_Extractor',
        'YandexAntivirus',
        'tracemyfile',
        'Linguee Bot',
        'Cliqzbot',
        'MaxPointCrawler',
        'Sogou web spider',
        'sysscan',
        'masscan',
        'fasthttp',
        'crawler',
        'scanner',
        'Qwantify',
        'serpstatbot',
        'MixnodeCache',
        'Puffin',
        'node-fetch',
        'coccocbot-web',
        'Nimbostratus-Bot',
        'coccocbot-web',
        'KOCMOHABT',
        'DomainStatsBot',
        'ActionExtension',
        'CFNetwork',
        'ZoomInformation Bot',
        'CrowdTanglebot',
        'DivulgaMais',
        'DaaS.sh',
        'PocketParser',
        'DragonFly',
        'PocketParser',
        'sqlmap',
        'Nmap',
        'SiteLockSpider',
        'AwarioSmartBot',
        'Vimprobable',
        'Iframely',
        'Project 25499',
        'Cloud mapping experiment',
        'BDCbot',
        'applebot',
        'petalbot',
        'CensysInspect',
        'expanseinc.com',
        'Embarcadero',
        'Baiduspider',
        'SeekportBot', //Mozilla/5.0 (compatible; SeekportBot; +https://bot.seekport.com)
        'Nikto', //Nikto Webserver Scanner https://github.com/sullo/nikto
        'gptbot', //Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.0; +https://openai.com/gptbot)
        'Bytespider', //Mozilla/5.0 (Linux; Android 5.0) AppleWebKit/537.36 (KHTML, like Gecko) Mobile Safari/537.36 (compatible; Bytespider; spider-feedback@bytedance.com)
    );

    /**
     * Get some information about browser, base on user agent
     * @SuppressWarnings(PHPMD.Superglobals)
     *
     * @param string $userAgent
     *
     * @return UserAgent
     */
    function __construct($userAgent = NULL)
    {
        //if not passed try to get from server
        if (!$userAgent && isset($_SERVER['HTTP_USER_AGENT']))
        {
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
        }

        $this->userAgent = trim($userAgent);

        //? or useragent less then 20 characters is allways bot
        if ($this->userAgent == '?' || strlen($this->userAgent) < 20)
        {
            $this->platform = self::PLATFORM_BOT;
            $this->developer = self::PLATFORM_BOT;
            $this->completeName = 'Unknown';
            $this->ubname = 'Unknown';
            $this->name = 'Unknown';
        }
        else if (trim($userAgent))
        {
            $this->parsePlatform();
            $this->parseBrowser();
            $this->parseVersion();
            $this->parseOthers();
        }
    }

    /**
     * Parse specif user agents
     */
    protected function parseOthers()
    {
        foreach (self::$serviceList as $service)
        {
            if (preg_match('/' . $service . '/i', $this->userAgent))
            {
                $this->platform = self::PLATFORM_BOT;
                $this->developer = self::PLATFORM_BOT;
                $this->completeName = $service;
                $this->ubname = $service;
                $this->name = $service;

                return;
            }
        }

        foreach (self::$botList as $bot)
        {
            if (preg_match('/' . $bot . '/i', $this->userAgent))
            {
                $this->platform = self::PLATFORM_BOT;
                $this->developer = self::PLATFORM_BOT;
                $this->completeName = $bot;
                $this->ubname = $bot;
                $this->name = $bot;

                return;
            }
        }
    }

    /**
     * Parse the platform from string
     */
    protected function parsePlatform()
    {
        if (preg_match('/android/i', $this->userAgent))
        {
            $this->platform = self::PLATFORM_ANDROID;
            $this->mobile = TRUE;
        }
        else if (preg_match('/iphone/i', $this->userAgent))
        {
            $this->platform = self::PLATFORM_IPHONE;
            $this->navigator = self::PLATFORM_IPHONE;
            $this->completeName = self::PLATFORM_IPHONE;
            $this->ubname = self::PLATFORM_IPHONE;
            $this->name = self::PLATFORM_IPHONE;
            $this->mobile = TRUE;
        }
        else if (preg_match('/ipad/i', $this->userAgent)) //count ipad like iphone
        {
            $this->platform = self::PLATFORM_IPHONE;
            $this->navigator = self::PLATFORM_IPHONE;
            $this->completeName = self::PLATFORM_IPHONE;
            $this->ubname = self::PLATFORM_IPHONE;
            $this->name = self::PLATFORM_IPHONE;
            $this->mobile = TRUE;
        }
        else if (preg_match('/blackberry/i', $this->userAgent))
        {
            $this->platform = self::PLATFORM_BLACKBERRY;
        }
        else if (preg_match('/bb10/i', $this->userAgent))
        {
            $this->platform = self::PLATFORM_BLACKBERRY;
        }
        else if (preg_match('/linux/i', $this->userAgent))
        {
            $this->platform = self::PLATFORM_LINUX;
        }
        //free BSD
        else if (preg_match('/FreeBSD/i', $this->userAgent))
        {
            $this->platform = self::PLATFORM_LINUX;
        }
        else if (preg_match('/OpenBSD/i', $this->userAgent))
        {
            $this->platform = self::PLATFORM_LINUX;
        }
        //chrome os
        else if (preg_match('/CrOS/i', $this->userAgent))
        {
            $this->platform = self::PLATFORM_LINUX;
        }
        elseif (preg_match('/macintosh|mac os x/i', $this->userAgent))
        {
            $this->platform = self::PLATFORM_MAC;
        }
        elseif (preg_match('/iemobile/i', $this->userAgent))
        {
            $this->platform = self::PLATFORM_WINDOWPHONE;
            $this->mobile = true;
        }
        elseif (preg_match('/windows|win32/i', $this->userAgent))
        {
            $this->platform = self::PLATFORM_WINDOWS;
        }

        //check for mobile word to mark browser as mobile
        if (preg_match('/mobile/i', $this->userAgent))
        {
            $this->mobile = true;
        }
    }

    /**
     * Parse browser name from userAgent
     */
    protected function parseBrowser()
    {
        // Next get the name of the useragent yes seperately and for good reason
        if ((preg_match('/MSIE/i', $this->userAgent) || preg_match('/Trident/i', $this->userAgent)) && !preg_match('/Opera/i', $this->userAgent))
        {
            $this->developer = UserAgent::DEVELOPER_MICROSOFT;
            $this->completeName = 'Internet Explorer';
            $this->ubname = "MSIE";
            $this->name = 'ie';

            if (!$this->platform)
            {
                $this->platform = UserAgent::PLATFORM_WINDOWS;
            }
        }
        elseif ($this->detectedBrowser(UserAgent::BROWSER_EDGE))
        {
            $this->developer = UserAgent::DEVELOPER_MICROSOFT;
        }
        elseif ($this->detectedBrowser(UserAgent::BROWSER_FIREFOX))
        {
            $this->developer = UserAgent::DEVELOPER_MOZILLA;
        }
        elseif ($this->detectedBrowser(UserAgent::BROWSER_LYNX))
        {
            $this->platform = UserAgent::PLATFORM_LINUX;
        }
        elseif ($this->detectedBrowser(UserAgent::BROWSER_ANDROID))
        {
            $this->mobile = true;
        }
        elseif (preg_match('/Opr/i', $this->userAgent)) //must be before Chrome
        {
            $this->developer = UserAgent::DEVELOPER_OPERA;
            $this->completeName = 'Opera';
            $this->ubname = "OPR";
            $this->name = 'opera';
        }
        elseif ($this->detectedBrowser('edg'))
        {
            $this->developer = UserAgent::DEVELOPER_MICROSOFT;
            $this->ubname = 'edge';
            $this->name = 'edge';
            $this->completeName = 'Edge';
        }
        elseif ($this->detectedBrowser(UserAgent::BROWSER_CHROME))
        {
            $this->developer = UserAgent::DEVELOPER_GOOGLE;
            $this->ubname = 'Chrome';
        }
        elseif ($this->detectedBrowser('Konqueror')) //chrome
        {
            $this->developer = UserAgent::DEVELOPER_GOOGLE;
            $this->ubname = 'Chrome';
        }
        elseif ($this->detectedBrowser('MobileSafari'))
        {
            $this->name = UserAgent::BROWSER_SAFARI;
            $this->completeName = UserAgent::BROWSER_SAFARI;
            $this->developer = UserAgent::DEVELOPER_APPLE;
        }
        elseif ($this->detectedBrowser('AppleWebKit'))
        {
            $this->name = UserAgent::BROWSER_SAFARI;
            $this->completeName = UserAgent::BROWSER_SAFARI;
            $this->developer = UserAgent::DEVELOPER_APPLE;
        }
        elseif ($this->detectedBrowser(UserAgent::BROWSER_SAFARI))
        {
            //Default android browser report Safari
            if ($this->platform == UserAgent::PLATFORM_ANDROID)
            {
                $this->name = UserAgent::BROWSER_ANDROID;
                $this->completeName = UserAgent::BROWSER_ANDROID;
                $this->developer = UserAgent::DEVELOPER_GOOGLE;
                $this->mobile = true;
            }

            $this->developer = UserAgent::DEVELOPER_APPLE;
        }
        elseif (preg_match('/Opera/i', $this->userAgent))
        {
            $this->developer = UserAgent::DEVELOPER_OPERA;
            $this->completeName = 'Opera';
            $this->ubname = "Opera";
            $this->name = 'opera';

            if (!$this->platform || $this->platform == '?')
            {
                $this->platform = UserAgent::PLATFORM_WINDOWS;
            }
        }
        elseif (preg_match('/Netscape/i', $this->userAgent))
        {
            $this->completeName = 'Netscape';
            $this->ubname = "Netscape";
            $this->name = 'netscape';
        }
        //qt navigator like chrome
        elseif (preg_match('/qt/i', $this->userAgent))
        {
            $this->name = UserAgent::BROWSER_CHROME;
            $this->completeName = UserAgent::BROWSER_CHROME;
            $this->developer = UserAgent::DEVELOPER_GOOGLE;
            $this->mobile = false;
        }
        //BonEcho is firefox beta
        elseif (preg_match('/BonEcho/i', $this->userAgent))
        {
            $this->name = UserAgent::BROWSER_FIREFOX;
            $this->completeName = UserAgent::BROWSER_FIREFOX;
            $this->developer = UserAgent::DEVELOPER_MOZILLA;
            $this->mobile = false;
        }
        elseif (preg_match('/Gecko/i', $this->userAgent))
        {
            $this->name = UserAgent::BROWSER_FIREFOX;
            $this->completeName = UserAgent::BROWSER_FIREFOX;
            $this->developer = UserAgent::DEVELOPER_MOZILLA;
            $this->mobile = false;
        }
        elseif (preg_match('/Mozilla/i', $this->userAgent))
        {
            $this->name = UserAgent::BROWSER_FIREFOX;
            $this->completeName = UserAgent::BROWSER_FIREFOX;
            $this->developer = UserAgent::DEVELOPER_MOZILLA;
            $this->mobile = false;
        }
    }

    /**
     * Function that optimize detecte browser code
     *
     * @param string $browser
     * @return boolean
     */
    protected function detectedBrowser($browser)
    {
        if (preg_match('/' . $browser . '/i', $this->userAgent))
        {
            $this->completeName = ucfirst($browser);
            $this->name = $browser;
            $this->ubname = ucfirst($browser);

            return true;
        }

        return false;
    }

    /**
     * finally get the correct version number
     */
    public function parseVersion()
    {
        //avoid preg match warning
        if (!$this->ubname || $this->ubname == '?' || !$this->userAgent)
        {
            return;
        }

        $known = array('Version', $this->ubname, 'other');
        $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        $matches = NULL;

        //TODO conferir validator w3c
        @preg_match($pattern, $this->userAgent, $matches);

        if (isset($matches['version']))
        {
            $this->version = $matches['version'];
        }

        // check if we have a number
        if ($this->version == NULL || $this->version == "")
        {
            $this->version = "?";
        }

        $this->simpleVersion = $this->getSimpleVersion();
    }

    /**
     * This function try to detect if is an old browser
     *
     * @return boolean
     */
    public function isOldBrowser()
    {
        if ($this->name == UserAgent::BROWSER_IE && $this->simpleVersion <= 9)
        {
            return TRUE;
        }

        if (($this->name == UserAgent::BROWSER_FIREFOX || $this->name == UserAgent::BROWSER_CHROME) && $this->simpleVersion < 30)
        {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Return the userAgent
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * Return the platform
     *
     * @return string
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * Return the version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Return the simple version
     * Normally a int number
     *
     * @return string
     */
    public function getSimpleVersion()
    {
        $version = $this->version;

        if (mb_stripos($version, '.') > 0)
        {
            $explode = explode('.', $version);
            return $explode[0];
        }

        return $version;
    }

    /**
     * Return the complete name of the browser
     *
     * @return string
     */
    public function getCompleteName()
    {
        return $this->completeName;
    }

    /**
     * Return the simplified name of browser
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return if browser is mobile
     *
     * @return boolean
     */
    public function isMobile()
    {
        return $this->mobile;
    }

    /**
     * Return true if is bot or service
     *
     * @return bool
     */
    public function isBotOrService()
    {
        return $this->getPlatform() == \DataHandle\UserAgent::PLATFORM_BOT || $this->getPlatform() == \DataHandle\UserAgent::PLATFORM_SERVICE;
    }

    /**
     * Convert it to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->userAgent;
    }

}
