<?php

namespace Whateverthing\Drinky\Command;

use Knp\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DrinkyAddressFixerCommand extends Command
{
    public function configure()
    {
        $this
            ->setName('drinky:fixer')
            ->setDescription('Cycle through all places and attempt to fix malformed addresses for geocoding');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $app      = $this->getSilexApplication();
        $db       = $app['db'];
        $places   = 'SELECT * FROM places WHERE geocode_address = \'\'';
        $update   = 'UPDATE places SET geocode_address = :addy WHERE id = :id';

        $result   = $db->executeQuery($places);
        $entries  = $result->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($entries as $entry) {
            $address = $this->getCleanAddress($entry);

            if (empty($address)) {
                continue;
            }

            $result = $db->executeQuery(
                $update,
                array(
                    'id' => $entry['id'],
                    'addy' => $address,
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
        $pattern = '/^.*[-,][ ]*([0-9A-Z]+) (.*)$/';
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