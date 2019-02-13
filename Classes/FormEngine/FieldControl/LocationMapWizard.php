<?php
declare(strict_types = 1);

namespace FriendsOfTYPO3\TtAddress\FormEngine\FieldControl;

/**
 * This file is part of the "tt_address" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
use TYPO3\CMS\Backend\Form\AbstractNode;

/**
 * Adds a wizard for location selection via map
 */
class LocationMapWizard extends AbstractNode
{
    /**
     * @return array
     */
    public function render(): array
    {
        $row = $this->data['databaseRow'];
        $paramArray = $this->data['parameterArray'];
        $resultArray = $this->initializeResultArray();

        $nameLon = $paramArray['itemFormElName'];
        $nameLat = str_replace('longitude', 'latitude', $nameLon);
        $nameLatActive = str_replace('data', 'control[active]', $nameLat);
        $tilesUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        $tilesAttribution = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';
        $geoCodeUrl = '';
        $gLat = '55.6760968';
        $gLon = '12.5683371';

        if ($row['latitude'] != '') {
            $lat = htmlspecialchars($row['latitude']);
        } else {
            $lat = '';
        }
        if ($row['longitude'] != '') {
            $lon = htmlspecialchars($row['longitude']);
        } else {
            $lon = '';
        }

        if ($row['latitude'] || $row['longitude'] == '') {
            // remove all after first slash in address (top, floor ...)
            $address = preg_replace('/^([^\/]*).*$/', '$1', $row['address']) . ' ';
            $address .= $row['city'];
            // if we have at least some address part (saves geocoding calls)
            if ($address != '') {
                // base url
                $geoCodeUrlBase = 'https://nominatim.openstreetmap.org/search/';
                $geoCodeUrlAddress .= $address;
                $geoCodeUrlCityOnly .= $row['city'];
                // urlparams for nominatim which are fixed.
                $geoCodeUrlQuery .= '?format=json&addressdetails=1&limit=1&polygon_svg=1';
                // replace newlines with spaces; remove multiple spaces
                $geoCodeUrl = trim(preg_replace('/\s\s+/', ' ', $geoCodeUrlBase . $geoCodeUrlAddress . $geoCodeUrlQuery));
                $geoCodeUrlShort = trim(preg_replace('/\s\s+/', ' ', $geoCodeUrlBase . $geoCodeUrlCityOnly . $geoCodeUrlQuery));
            } else {
                $geoCodeUrl = '';
            }
        }

        // due to deprecations in TYPO3 v8 we now need two calls for one icon, this one is the first:
        $closeIconPathAbsolute = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:core/Resources/Public/Icons/T3Icons/actions/actions-close.svg');

        $resultArray['iconIdentifier'] = 'location-map-wizard';
        $resultArray['title'] = $GLOBALS['LANG']->sL('LLL:EXT:tt_address/Resources/Private/Language/locallang_db.xlf:tt_address.locationMapWizard');
        $resultArray['linkAttributes']['class'] = 'locationMapWizard ';
        $resultArray['linkAttributes']['id'] = 'location-map-container-a';
        $resultArray['linkAttributes']['data-lat'] = $lat;
        $resultArray['linkAttributes']['data-lon'] = $lon;
        $resultArray['linkAttributes']['data-glat'] = $gLat;
        $resultArray['linkAttributes']['data-glon'] = $gLon;
        $resultArray['linkAttributes']['data-geocodeurl'] = $geoCodeUrl;
        $resultArray['linkAttributes']['data-geocodeurlshort'] = $geoCodeUrlShort;
        $resultArray['linkAttributes']['data-namelat'] = htmlspecialchars($nameLat);
        $resultArray['linkAttributes']['data-namelon'] = htmlspecialchars($nameLon);
        $resultArray['linkAttributes']['data-namelat-active'] = htmlspecialchars($nameLatActive);
        $resultArray['linkAttributes']['data-tiles'] = htmlspecialchars($tilesUrl);
        $resultArray['linkAttributes']['data-copy'] = $tilesAttribution;
        $resultArray['linkAttributes']['data-icon-close'] = \TYPO3\CMS\Core\Utility\PathUtility::getAbsoluteWebPath($closeIconPathAbsolute);
        $resultArray['stylesheetFiles'][] = 'EXT:tt_address/Resources/Public/Contrib/leaflet-core-1.4.0.css';
        $resultArray['stylesheetFiles'][] = 'EXT:tt_address/Resources/Public/Backend/LocationMapWizard/leafletBackend.css';
        $resultArray['requireJsModules'][] = 'TYPO3/CMS/TtAddress/leaflet-core-1.4.0';
        $resultArray['requireJsModules'][] = 'TYPO3/CMS/TtAddress/LeafletBackend';

        return $resultArray;
    }
}
