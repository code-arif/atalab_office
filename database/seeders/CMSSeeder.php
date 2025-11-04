<?php

namespace Database\Seeders;

use App\Models\CMS;
use App\Enums\PageEnum;
use App\Enums\SectionEnum;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CmsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /**
         * Hero section
         */
        // Home Page - Hero Section
        $data = [
            [
                'page'            => PageEnum::HOME->value,
                'section'         => SectionEnum::HERO->value,
                'name'            => 'item',
                'slug'            => null,
                'title'           => 'We the People, United, Touching Lives, One Draw at a time!',
                'sub_title'       => null,
                'description'     => null,
                'sub_description' => null,
                'bg'              => null,
                'image'           => asset('default/placeholder-image.avif'),
                'btn_text'        => null,
                'btn_link'        => null,
                'btn_color'       => null,
                'metadata'        => null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],

            /**
             * Home page - redistrubution table section
             */
            [
                'page'            => PageEnum::HOME->value,
                'section'         => SectionEnum::REDISTRIBUTION_TABLE->value,
                'name'            => 'item',
                'slug'            => null,
                'title'           => 'Redistribution Table',
                'sub_title'       => null,
                'description'     => 'Note: All figures are illustrative. Actual distribution will be determined Sunday between 3-5 PM.',
                'sub_description' => null,
                'bg'              => null,
                'image'           => '',
                'btn_text'        => null,
                'btn_link'        => null,
                'btn_color'       => null,
                'metadata'        => null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],

            /**
             * Home page - parcentage section
             */
            [
                'page'    => PageEnum::HOME->value,
                'section' => SectionEnum::PARCENTAGE->value,
                'name'    => 'item',
                'slug'    => '',
                'title'   => 'Admin Fee Percentage (Per Recipient Basis)',
                'sub_title'       => null,
                'description'     => 'Flat Admin Fee per Selected Recipient: $750. Example: If redistribution amount per recipient: $10,000 Admin Fee as 7.5% of Redistribution $750. The admin fee is subtracted directly from each selected participant /+ any wire fee / certified mail fees ($25 to $50).',
                'sub_description' => null,
                'bg'              => null,
                'image'           => '',
                'btn_text'        => null,
                'btn_link'        => null,
                'btn_color'       => null,
                'metadata'        => null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],

            /**
             * home page - selected name section
             */
            [
                'page'    => PageEnum::HOME->value,
                'section' => SectionEnum::SELECTED_NAME->value,
                'name'    => 'item',
                'slug'    => '',
                'title'   => 'What to Do If your Name is Selected',
                'sub_title'       => 'null',
                'description'     => 'Claiming Your Distribution. The drawing will take place on Sunday at 5:00 PM. If your name is selected, we will attempt to contact you once between 5:00 PM and 8:00 PM via email. Please monitor your spam folder, text message and a one-time phone call. We will not make a second attempt. After this window, it becomes your responsibility to contact The Dignity Draw. Inc. to claim your selection. Steps to Claim: Contact us by Monday at 5:00 PM (Pacific Time). Verify your identity with a government-issued ID. Sign required documents via DocuSign. Submit a voided check for bank verification. Payment Methods:Check mailed to the recipient. Wire transfer to a US bank account (fees apply). Note: If you miss the deadline, your distribution will be forfeited, and a replacement recipient will be selected.',
                'sub_description' => null,
                'bg'              => null,
                'image'           => asset('default/placeholder-image.avif'),
                'btn_text'        => null,
                'btn_link'        => null,
                'btn_color'       => null,
                'metadata'        => null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],

            /**
             * home page - quote section
             */
            [
                'page'            => PageEnum::HOME->value,
                'section'         => SectionEnum::QUOTE->value,
                'name'            => 'item',
                'slug'            => null,
                'title'           => '“We didn’t start with a product. We started with a question: What if economic redistribution could feel like recognition?”',
                'sub_title'       => null,
                'description'     => null,
                'sub_description' => null,
                'bg'              => null,
                'image'           => null,
                'btn_text'        => null,
                'btn_link'        => null,
                'btn_color'       => null,
                'metadata'        => null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],

            /**
             * home page - our story section
             */
            [
                'page'            => PageEnum::HOME->value,
                'section'         => SectionEnum::OUR_STORY->value,
                'name'            => 'item',
                'slug'            => null,
                'title'           => 'Our Story',
                'sub_title'       => null,
                'description'     => 'A New Approach to Economic Redistribution. The Dignity Draw was born out of frustration with the current systems of economic support. We didn’t want to rely on charity models or lotteries with impractical adds. Our solution: a non-profit platform for name-based redistribution that works by giving back directly to participants. Mo hoops to jump through, no hidden agendas. Every draw is open, fair, and transparent. We’re not just creating a campaign: we’re building a system of dignity.',
                'sub_description' => null,
                'bg'              => null,
                'image'           => '',
                'btn_text'        => null,
                'btn_link'        => null,
                'btn_color'       => null,
                'metadata'        => null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],

            /**
             * home page - testimonial section
             */
            [
                'page'            => PageEnum::HOME->value,
                'section'         => SectionEnum::TESTIMONIAL->value,
                'name'            => 'item',
                'slug'            => null,
                'title'           => 'Touching Lives one draw at a time',
                'sub_title'       => null,
                'description'     => null,
                'sub_description' => null,
                'bg'              => null,
                'image'           => null,
                'btn_text'        => null,
                'btn_link'        => null,
                'btn_color'       => null,
                'metadata'        => null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],

            /**
             * home page - gallery section
             */
            [
                'page'            => PageEnum::HOME->value,
                'section'         => SectionEnum::GALLERY->value,
                'name'            => 'item',
                'slug'            => null,
                'title'           => null,
                'sub_title'       => null,
                'description'     => null,
                'sub_description' => null,
                'bg'              => null,
                'image'           => asset('default/placeholder-image.avif'),
                'btn_text'        => null,
                'btn_link'        => null,
                'btn_color'       => null,
                'metadata'        => null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],

            /**
             * home page - desclaimer seciton
             */
            [
                'page'            => PageEnum::HOME->value,
                'section'         => SectionEnum::DISCLAIMER->value,
                'name'            => 'item',
                'slug'            => null,
                'title'           => 'Desclaimer',
                'sub_title'       => null,
                'description'     => 'Disclosures: The Dignity Draw Pending 501(c)(3) Status. The Dignity Draw, Inc. is a non-profit corporation registered in the State of California. Our application for federal tax-exempt status under IRS Section 501(c)(3) is currently under review. While we await final determination, we are operating in accordance with IRS guidelines for pending non-profit organizations. All funds received are used exclusively for charitable purposes consistent with our mission. Please note: Contributions made during this pending period may not be tax-deductible until our exemption is officially granted. The Dignity Draw does not solicit or endorse any religious or community institution. Inclusion reflects verified participation and recognition, not affiliation or promotion. We support families in immediate need nominated by churches, mosques, synagogues, shelters, and verified community groups. Every nomination is reviewed with care, dignity, and transparency. Participation is never coerced, and every draw affirms the agency of those it uplifts. Ethical Safeguards Statement: “As part of our onboarding and public-facing materials, The Dignity Draw affirms its categorical condemnation of harmful fund usage. This includes harmful tools, illicit substances, and any activity that endangers or exploits. While we cannot control outcomes, we begin with trust and reinforce our values through participant pledges, onboarding language, and community affirmation.”',
                'sub_description' => null,
                'bg'              => null,
                'image'           => '',
                'btn_text'        => null,
                'btn_link'        => null,
                'btn_color'       => null,
                'metadata'        => null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],

            /**
             * Home page - we believe section
             */
            [
                'page'    => PageEnum::HOME->value,
                'section' => SectionEnum::WE_BELIEVE->value,
                'name'    => 'item',
                'slug'    => null,
                'title'   => 'We Believe',
                'sub_title'       => null,
                'description'     => '“The Scale of Justice Must be balanced in every Relationship” “We must not violate one another to reach our goals and objectives”',
                'sub_description' => null,
                'bg'              => null,
                'image'           => asset('default/placeholder-image.avif'),
                'btn_text'        => null,
                'btn_link'        => null,
                'btn_color'       => null,
                'metadata'        => null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],

            /**
             * Home page - founder statement
             */
            [
                'page'    => PageEnum::HOME->value,
                'section' => SectionEnum::FOUNDER_STATEMENT->value,
                'name'    => 'item',
                'slug'    => null,
                'title'   => 'Founder Statement',
                'sub_title'       => 'We the People, United, Touching Lives, One Draw at a Time.',
                'description'     => '“We built The Dignity Draw for one reason: to touch lives and give back to the communities with Dignity. Not through spectacle, but intention. Every name is honoured. Every story matters.This is redistribution with heart. This is legacy with clarity. This is us, we the people, moving forward, one draw at a time. -Ali Bozorgi Talab, Founder of The Dignity Draw.',
                'sub_description' => null,
                'bg'              => null,
                'image'           => '',
                'btn_text'        => null,
                'btn_link'        => null,
                'btn_color'       => null,
                'metadata'        => null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],

            /**
             * Our story page - hero section
             */
            [
                'page'    => PageEnum::OUR_STORY->value,
                'section' => SectionEnum::HERO->value,
                'name'    => 'item',
                'slug'    => null,
                'title'   => 'Our story',
                'sub_title'       => null,
                'description'     => 'The Beginning. We didn’t start with a product. We started with a question: What if economic redistribution could feel like recognition? The Dignity Draw, Inc. Frustrated by the way social welfare systems operate—with endless red tape, gatekeeping, and delays—I wanted to create something radically simple. Too often, even well-meaning non-profits rely on donation models or lottery systems with odds like 1 in 41 million… or worse, 1 in 292 million. That’s not hope. That’s a very long shot. A dream that may never come true. So I built The Dignity Draw to do what those systems won’t: Give back directly. No applications. No credit checks. The Dignity Draw was born from a simple but radical idea: that names carry meaning, and that verified participation deserves transparency. We weren’t interested in gimmicks, sweepstakes, or anonymous lotteries. We wanted a system where every draw was archived, every outcome disclosed, and every participant seen. 🧱 What We Built. We created a non-profit platform for name-based redistribution—where people enter with their legal name, and draws are conducted with full odds disclosure, tax clarity, and officer accountability. Every draw is timestamped, archived, and made public. No purchase is necessary. No data is sold. No dignity is compromised. This isn’t just a campaign. It’s a structure. Verified identity. Transparent odds. Archived outcomes. Officer compensation disclosed. Tax policy published. 🫱🏽‍🫲🏿 Who It’s For: The Dignity Draw is for anyone who believes redistribution should be rooted in recognition, not randomness. It’s for those who’ve felt unseen in systems that claim to serve them. It’s for people who want to witness, not just win. And it’s for every name that deserves to be honoured, whether drawn or not. 🧾 Why It’s Different The Dignity Draw is for anyone who believes redistribution should be rooted in recognition, not randomness. It’s for those who’ve felt unseen in systems that claim to serve them. It’s for people who want to witness, not just win. And it’s for every name that deserves to be honoured, whether drawn or not.',
                'sub_description' => null,
                'bg'              => null,
                'image'           => '',
                'btn_text'        => null,
                'btn_link'        => null,
                'btn_color'       => null,
                'metadata'        => null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],

            /**
             * how it works page - hero section
             */
            [
                'page'    => PageEnum::HOW_IT_WORKS->value,
                'section' => SectionEnum::HERO->value,
                'name'    => 'item',
                'slug'    => null,
                'title'   => 'How it works',
                'sub_title'       => null,
                'description'     => 'Verified Redistribution, One Name at a Time. The Dignity Draw is a name-based economic redistribution system. It’s not random. It’s not anonymous. Every draw is structured, documented, and publicly auditable. Step-by-Step Process. Name Submission & Contribution Participants submit their full legal name through a secure onboarding flow. A verified financial contribution is required to enter the draw. Contributions are documented, receipted, and used to fund redistribution, officer compensation, and operational costs. No usernames. No aliases. Every entry is verified. Draw Preparation Names are compiled into a draw ledger. Odds are calculated and disclosed in advance. Officer compensation and operational costs are transparently documented. Public Draw Execution Draws are conducted on a scheduled basis. Recipients are selected based on verified odds. Results are published in the Draw Archive. Recipient Notification & Disclosure Recipients are notified and provided with full tax policy and reporting guidance. All disclosures are versioned and timestamped. Legacy Archiving Every draw is recorded. Every name is preserved. Every redistribution is part of a verified public benefit.',
                'sub_description' => null,
                'bg'              => null,
                'image'           => '',
                'btn_text'        => null,
                'btn_link'        => null,
                'btn_color'       => null,
                'metadata'        => null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]
        ];

        foreach ($data as $row) {
            CMS::create($row);
        }
    }
}
