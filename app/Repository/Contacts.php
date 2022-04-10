<?php

namespace App\Repository;

use App\Model\Contact;
use App\Exception\NotFound;
use App\Exception\WrongInput;
use App\Repository;
use PDO;

class Contacts extends Repository
{
    function createForSource(int $source, array $items = [])
    {
        $lastday = date(self::FMT_MYSQLDT, strtotime('-24 hours'));
        $now = date(self::FMT_MYSQLDT);
        $inserted = 0;
        $phones = [];

        foreach ($items as $i => &$item) {
            $it = Contact::ensure($item);

            $item['phone'] = self::filterPhone($it['phone']);
            if (
                !filter_var($item['phone'], FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/\d{10,}/']])
                || !filter_var($it['email'], FILTER_VALIDATE_EMAIL)
                || !filter_var($it['name'], FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/[\s\S]{3,}/']])
            ) {
                unset($items[$i]);
                continue;
            }

            $phones[] = $item['phone'];
        }
        unset($item);

        $pstmt = $this->db->prepare(sprintf(
            "SELECT phone FROM contacts WHERE phone in (%s) AND sourceid = ? AND created >= ?",
            # (?,?,?,...)
            implode(
                ',',
                array_fill(0, count($phones), '?')
            )
        ));
        $pstmt->execute([
            ...$phones,
            $source,
            $lastday,
        ]);

        $pexist = $pstmt->fetchAll(PDO::FETCH_COLUMN);
        $istmt = $this->db->prepare(
            "INSERT INTO contacts (name,phone,email,created,sourceid) values (?,?,?,?,?)",
            [PDO::ERRMODE_SILENT]
        );

        $this->db->beginTransaction();
        foreach ($items as $item) {
            $it = Contact::ensure($item);
            if (in_array($it['phone'], $pexist)) continue;
            $ok = $istmt->execute([
                $it['name'],
                $it['phone'],
                $it['email'],
                $now,
                $source,
            ]);
            if ($ok) $inserted++;
        }
        $this->db->commit();

        return $inserted;
    }

    /**
     * @param $num
     * @return array
     * @throws NotFound
     * @throws WrongInput
     */
    function getByPhone($num)
    {
        $num = self::filterPhone($num);
        if (empty($num)) throw new WrongInput;

        $stmt = $this->db->prepare("SELECT name,phone,email FROM contacts WHERE phone = ?");
        $stmt->execute([$num]);
        $contacts = $stmt->fetchAll(PDO::FETCH_CLASS, Contact::class);
        if (empty($contacts)) throw new NotFound;

        return $contacts;
    }

    protected static function filterPhone($phone): int
    {
        return (int)substr(
            trim((string)$phone),
            -10
        );
    }
}