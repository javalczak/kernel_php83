<?php
declare(strict_types=1);

namespace App\Schema;

use Engine\Database;

final class SchemaInstaller
{
    public function __construct(
        private Database $db
    ) {}

    public function install(): void
    {
        $this->db->query(AdminSchema::createTableSql());
        $this->db->query(AdminLoginAttemptSchema::createTableSql());
        $this->db->query(LocationSchema::createTableSql());
        $this->db->query(FeatureSchema::createTableSql());
        $this->db->query(ActivitySchema::createTableSql());
        $this->db->query(PropertyTypeSchema::createTableSql());
        $this->db->query(UserSchema::createTableSql());
        $this->db->query(PropertySchema::createTableSql());
        $this->db->query(PropertyFeatureSchema::createTableSql());
        $this->db->query(PropertyActivitySchema::createTableSql());
        $this->db->query(PropertyPictureSchema::createTableSql());
        $this->db->query(CouponSchema::createTableSql());
        $this->db->query(ContentPageSchema::createTableSql());
    }

    public function uninstall(): void
    {
        $this->db->query(ContentPageSchema::dropTableSql());
        $this->db->query(CouponSchema::dropTableSql());
        $this->db->query(PropertyPictureSchema::dropTableSql());
        $this->db->query(PropertyActivitySchema::dropTableSql());
        $this->db->query(PropertyFeatureSchema::dropTableSql());
        $this->db->query(PropertySchema::dropTableSql());
        $this->db->query(UserSchema::dropTableSql());
        $this->db->query(PropertyTypeSchema::dropTableSql());
        $this->db->query(ActivitySchema::dropTableSql());
        $this->db->query(FeatureSchema::dropTableSql());
        $this->db->query(LocationSchema::dropTableSql());
        $this->db->query(AdminLoginAttemptSchema::dropTableSql());
        $this->db->query(AdminSchema::dropTableSql());
    }
}
