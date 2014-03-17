<?php

namespace Whateverthing\Drinky\Command;

use Knp\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DrinkyDataCommand extends Command
{
    public function configure()
    {
        $this
            ->setName('drinky:data')
            ->setDescription('Fetch the latest BC Licensed Businesses List and refresh the database');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $app      = $this->getSilexApplication();
        $db       = $app['db'];
        $root     = $this->getProjectDirectory();
        $dataFile = $root . '/data/data_store.csv';
        $fetch    = 'SELECT * FROM places WHERE name = :establishmentname AND address1 = :locationaddressline1 AND type = :licencetype';
        $insert   = 'INSERT INTO places (' .
                    'name, address1, address2, city, postal, mail_address1, ' .
                    'mail_address2, mail_city, mail_prov, mail_postal, type, capacity, date_added' .
                    ') VALUES (' .
                    ':establishmentname, :locationaddressline1, :locationaddressline2, :locationaddresscity, :locationpostalcode, :mailingaddressline1, ' .
                    ':mailingaddressline2, :mailingaddresscity, :mailingprov, :mailingpostalcode, :licencetype, :capacity, NOW()' .
                    ')';

        if (!file_exists($dataFile)) {
            $rawData = file_get_contents('http://pub.data.gov.bc.ca/datasets/177464/bc_liquor_licensed_establishments.csv');
            file_put_contents($dataFile, $rawData);
        }

        $file = fopen($dataFile, 'r');

        $headers = fgetcsv($file);

        array_walk($headers, function (&$data) { $data = str_replace(' ', '', $data);});

        while ($row = fgetcsv($file)) {
            $place = array_combine($headers, $row);
            $fetchParams = array_intersect_key($place, array_flip(array('establishmentname', 'locationaddressline1', 'licencetype')));

            $result = $db->executeQuery($fetch, $fetchParams);

            $existingPlace = $result->fetchAll(\PDO::FETCH_ASSOC);

            if ($existingPlace) {
                continue;
            }

            $result = $db->executeQuery($insert, $place);
        }

        fclose($file);
    }
} 