<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\FooterLink;
use App\Models\FooterLinkCategory;
use App\Models\Setting;
use App\Models\SocialLink;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. General Settings
        Setting::create([
            'site_name' => 'Kolmox',
            'site_description' => 'Servicio de transporte de encomiendas rápido y seguro a nivel nacional.',
            'keywords' => 'encomiendas, transporte, bolivia, envios, paqueteria',
            'support_email' => 'soporte@kolmox.com',
            'support_phone' => '+591 2 5252525',
            'address' => 'Calle Bolívar #123, Oruro, Bolivia',
            'terms_and_conditions' => 'Términos y condiciones iniciales...',
            'privacy_policy' => 'Política de privacidad inicial...',
        ]);

        // 2. Social Links
        $socials = [
            ['platform' => 'facebook', 'url' => 'https://facebook.com/kolmox', 'active' => true],
            ['platform' => 'instagram', 'url' => 'https://instagram.com/kolmox', 'active' => true],
            ['platform' => 'tiktok', 'url' => 'https://tiktok.com/@kolmox', 'active' => false],
            ['platform' => 'whatsapp', 'url' => 'https://wa.me/59170000000', 'active' => true],
        ];

        foreach ($socials as $social) {
            SocialLink::create($social);
        }

        // 3. FAQs
        $faqs = [
            [
                'question' => '¿Cómo rastreo mi envío?',
                'answer' => 'Puede rastrear su envío ingresando el código de guía en la página de inicio.',
                'active' => true,
                'order_index' => 1
            ],
            [
                'question' => '¿Cuáles son los tiempos de entrega?',
                'answer' => 'Los tiempos varían según el destino, generalmente entre 24 a 48 horas.',
                'active' => true,
                'order_index' => 2
            ],
            [
                'question' => '¿Realizan envíos puerta a puerta?',
                'answer' => 'Sí, contamos con servicio de recojo y entrega a domicilio.',
                'active' => true,
                'order_index' => 3
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::create($faq);
        }

        // 4. Footer Links Categories & Links
        $categories = [
            'services' => [
                ['name' => 'Rastreo de Envíos', 'href' => '/tracking', 'order' => 1],
                ['name' => 'Cotizar Envío', 'href' => '/quote', 'order' => 2],
                ['name' => 'Cobertura', 'href' => '/coverage', 'order' => 3],
            ],
            'company' => [
                ['name' => 'Sobre Nosotros', 'href' => '/about', 'order' => 1],
                ['name' => 'Nuestras Oficinas', 'href' => '/offices', 'order' => 2],
                ['name' => 'Trabaja con Nosotros', 'href' => '/careers', 'order' => 3],
            ],
            'support' => [
                ['name' => 'Centro de Ayuda', 'href' => '/help', 'order' => 1],
                ['name' => 'Preguntas Frecuentes', 'href' => '/faqs', 'order' => 2],
                ['name' => 'Contacto', 'href' => '/contact', 'order' => 3],
            ],
            'legal' => [
                ['name' => 'Términos y Condiciones', 'href' => '/terms', 'order' => 1],
                ['name' => 'Política de Privacidad', 'href' => '/privacy', 'order' => 2],
            ],
        ];

        foreach ($categories as $catName => $links) {
            $category = FooterLinkCategory::create(['name' => $catName]);
            foreach ($links as $link) {
                $category->links()->create($link);
            }
        }
    }
}
