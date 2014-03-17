<?php

namespace Whateverthing\Drinky\Command;

use Knp\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DrinkyGeocoderCommand extends Command
{
    public function configure()
    {
        $this
            ->setName('drinky:geocoder')
            ->setDescription('Cycle through all un-geocoded places and attempt to place them on the map');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $app      = $this->getSilexApplication();
        $db       = $app['db'];
        $geocoder = $app['geocoder'];
        $places   = 'SELECT * FROM places WHERE LOWER(city) in ("' .
                    implode(
                        '", "',
                        array(
                            'bamfield',
                            'brentwood bay',
                            'central saanich',
                            'colwood',
                            'ladysmith',
                            'duncan',
                            'esquimalt',
                            'langford',
                            'mill bay',
                            'north saanich',
                            'oak bay',
                            'saanich',
                            'saanichton',
                            'shawnigan lake',
                            'sidney',
                            'sooke',
                            'victoria',
                        )
                    ) .
                    '") AND geocode_raw = \'\' AND (latitude=0 OR longitude=0 OR longitude IS NULL or latitude IS NULL) ORDER BY RAND() LIMIT 50';
        $update   = 'UPDATE places SET latitude = :lat, longitude = :long, geocode_raw = :raw WHERE id = :id';
        $updateFail = 'UPDATE places SET geocode_raw = :msg WHERE id = :id';

        $result   = $db->executeQuery($places);
        $entries  = $result->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($entries as $entry) {
            try {
                $geocodeResult = $geocoder->geocode($entry['geocode_address']);
                sleep(2);
            } catch (\Exception $e) {
                $result = $db->executeQuery(
                    $updateFail,
                    array(
                        'id' => $entry['id'],
                        'msg' => $e->getMessage(),
                    )
                );
                sleep(2);
                continue;
            }

            $result = $db->executeQuery(
                $update,
                array(
                    'id' => $entry['id'],
                    'lat'=>$geocodeResult->getLatitude(),
                    'long' => $geocodeResult->getLongitude(),
                    'raw' => json_encode((array)$geocodeResult),
                )
            );
        }
    }

    public function getCleanAddress($entry)
    {
        $streetAddress = empty($entry['address2']) ? $entry['address1'] : $entry['address1'] . ' ' . $entry['address2'];

        // remove pound signs
        $streetAddress = ltrim($streetAddress, '# ');

        // 1 - 11151 Horseshoe Way
        // 1, 32650 Logan Avenue
        // 1-  2740 Dundas Road
        $pattern = '/^.*[-,][ ]+([0-9A-Z]+) (.*)$/';
        $replacement = '$1 $2';
        $streetAddress = preg_replace($pattern, $replacement, $streetAddress, 1);

        // 1 2220 Bowen Road
        $pattern = '/[0-9]+ ([0-9]+) (.*)/';
        $replacement = '$1 $2';
        $streetAddress = preg_replace($pattern, $replacement, $streetAddress, 1);

        $address = array(
            $streetAddress,
            $entry['city'],
            'BC',
            'Canada',
        );

        $cleanAddress = implode(', ', $address);

        return $cleanAddress;
    }
} 