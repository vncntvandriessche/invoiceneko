<?php

namespace Tests\Browser;

use App\Models\ItemTemplate;
use App\Models\Invoice;
use Carbon\Carbon;
use Log;
use App\Models\Client;
use Faker\Factory as Faker;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class InvoiceTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * A Dusk test example.
     *
     * @return void
     * @throws \Throwable
     */
    public function test_creating_an_invoice()
    {
        $client = factory(Client::class)->create();
        $itemTemplate = factory(ItemTemplate::class)->create([
            'company_id' => $client->company->id
        ]);

        //Need to assign the company_id to the user
        $client->company->owner->company_id = $client->company->id;
        $client->company->owner->save();

        $faker = Faker::create();

        $this->browse(function (Browser $browser) use ($faker, $client, $itemTemplate) {
            $browser->visit('/signin')
                ->type('username', $client->company->owner->email)
                ->type('password', 'secret')
                ->press('SIGN IN')
                ->assertPathIs('/dashboard')
                ->visit('/invoices')
                ->click("a[href='{$this->baseUrl()}/invoice/create']")
                ->assertPathIs('/invoice/create')
                ->type('nice_invoice_id', substr($faker->slug, 0, 20) . 'sasdf')
                ->type('netdays', $faker->numberBetween($min = 1, $max = 60))
                ->type('item_quantity[]', $faker->numberBetween($min = 1, $max = 999999999))
                ->type('item_price[]', $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 999999999999));
            $browser
                ->script('jQuery("#client_id").selectize()[0].selectize.setValue(1);');
            $browser
                ->script('jQuery("#date").datepicker("setDate", new Date());jQuery("#date").val("' . Carbon::now()->format('j F, Y') . '");');
            $browser
                ->script('jQuery("#item_name_0").selectize()[0].selectize.setValue("' . addslashes($itemTemplate->name) .'");');
            $browser->pause(2000);
            $browser
                ->press('CREATE')
                ->assertPresent('#invoice-action-container');
            $browser->script('jQuery(".signmeout-btn").click()');
            $browser->assertPathIs('/signin');
        });
    }

    public function test_adding_a_second_invoice_item()
    {
        $client = factory(Client::class)->create();
        $itemTemplates = factory(ItemTemplate::class, 5)->create([
            'company_id' => $client->company->id
        ]);

        //Need to assign the company_id to the user
        $client->company->owner->company_id = $client->company->id;
        $client->company->owner->save();

        $faker = Faker::create();

        $this->browse(function (Browser $browser) use ($faker, $client, $itemTemplates) {
            $browser->visit('/signin')
                ->type('username', $client->company->owner->email)
                ->type('password', 'secret')
                ->press('SIGN IN')
                ->assertPathIs('/dashboard')
                ->visit('/invoices')
                ->click("a[href='{$this->baseUrl()}/invoice/create']")
                ->assertPathIs('/invoice/create')
                ->type('nice_invoice_id', substr($faker->slug, 0, 20) . 'sasdf')
                ->type('netdays', $faker->numberBetween($min = 1, $max = 60))
                ->type('item_quantity[]', $faker->numberBetween($min = 1, $max = 999999999))
                ->type('item_price[]', $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 999999999999));
            $browser
                ->script('jQuery("#client_id").selectize()[0].selectize.setValue(1);');
            $browser
                ->script('jQuery("#date").datepicker("setDate", new Date());jQuery("#date").val("' . Carbon::now()->format('j F, Y') . '");');
            $browser
                ->script('jQuery("#item_name_0").selectize()[0].selectize.setValue("' . addslashes($itemTemplates[0]->name) .'");');
            $browser
                ->click('a[id="invoice-item-add"]');
            $browser
                ->script('jQuery("#item_name_1").selectize()[0].selectize.setValue("' . addslashes($itemTemplates[1]->name) .'");');
            $browser->pause(2000);
            $browser
                ->press('CREATE')
                ->assertPresent('#invoice-action-container');
            $browser->script('jQuery(".signmeout-btn").click()');
            $browser->assertPathIs('/signin');
        });
    }
}