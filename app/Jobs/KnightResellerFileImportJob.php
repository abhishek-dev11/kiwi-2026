<?php

namespace App\Jobs;

// Knight Reseller's file is WooCommerce-style (see WooCommerceFileImportJob for the shared
// variable / variation handling) and carries its own categories (Office Furniture > ...),
// which are used as given. Its variation SKUs don't start with the parent's SKU (parent
// "SWIFT", variations "KG_SWT_B__ASS" / "KG_SWT_B__KD"), so a colour / option name shared
// by several SKUs of one product (e.g. "Black Fabric" assembled and knock-down) is
// labelled with each whole SKU: "Black Fabric (KG_SWT_B__ASS)".
class KnightResellerFileImportJob extends WooCommerceFileImportJob
{
    protected function indexName(): string
    {
        return 'knight';
    }

    protected function labelSizes(array $entries): array
    {
        return $this->labelSizesWithSkuCodes($entries);
    }
}
