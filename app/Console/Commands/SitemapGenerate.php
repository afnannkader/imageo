<?php

namespace App\Console\Commands;

use App\Models\BlogArticle;
use App\Models\BlogCategory;
use App\Models\FooterMenu;
use App\Models\GeneratedImage;
use App\Models\NavbarMenu;
use App\Models\Page;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Url;

class SitemapGenerate extends Command
{
    protected $signature = 'app:sitemap-generate';

    protected $description = 'Generate website sitemap.xml';

    public function handle()
    {
        $limit = 40000;
        $sitemapIndex = SitemapIndex::create();
        $chunk = 1;

        $sitemap = Sitemap::create();

        $writeSitemapFile = function ($sitemap, $chunk, $sitemapIndex) {
            $filename = "sitemap_{$chunk}.xml";
            $sitemap->writeToFile($this->store_path($filename));
            $sitemapIndex->add("/$filename");
        };

        $navbarLinks = NavbarMenu::all();
        foreach ($navbarLinks as $navbarLink) {
            $sitemap->add(
                Url::create($navbarLink->link)
                    ->setLastModificationDate(Carbon::yesterday())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                    ->setPriority(0.1)
            );

            if (count($sitemap->getTags()) >= $limit) {
                $writeSitemapFile($sitemap, $chunk, $sitemapIndex);
                $chunk++;
                $sitemap = Sitemap::create();
            }
        }

        $generatedImages = GeneratedImage::public()->get();
        foreach ($generatedImages as $generatedImage) {
            $sitemap->add(
                Url::create(route('images.show', hashId($generatedImage->id)))
                    ->setLastModificationDate(Carbon::yesterday())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                    ->setPriority(0.1)
                    ->addImage($generatedImage->getMainImageLink())
            );

            if (count($sitemap->getTags()) >= $limit) {
                $writeSitemapFile($sitemap, $chunk, $sitemapIndex);
                $chunk++;
                $sitemap = Sitemap::create();
            }
        }

        $blogCategories = BlogCategory::all();
        foreach ($blogCategories as $blogCategory) {
            $sitemap->add(
                Url::create(route('blog.category', $blogCategory->slug))
                    ->setLastModificationDate(Carbon::yesterday())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                    ->setPriority(0.1)
            );

            if (count($sitemap->getTags()) >= $limit) {
                $writeSitemapFile($sitemap, $chunk, $sitemapIndex);
                $chunk++;
                $sitemap = Sitemap::create();
            }
        }

        $blogArticles = BlogArticle::all();
        foreach ($blogArticles as $blogArticle) {
            $sitemap->add(
                Url::create(route('blog.article', $blogArticle->slug))
                    ->setLastModificationDate(Carbon::yesterday())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                    ->setPriority(0.1)
                    ->addImage(asset($blogArticle->image))
            );

            if (count($sitemap->getTags()) >= $limit) {
                $writeSitemapFile($sitemap, $chunk, $sitemapIndex);
                $chunk++;
                $sitemap = Sitemap::create();
            }
        }

        $pages = Page::all();
        foreach ($pages as $page) {
            $sitemap->add(
                Url::create(route('page', $page->slug))
                    ->setLastModificationDate(Carbon::yesterday())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                    ->setPriority(0.1)
            );

            if (count($sitemap->getTags()) >= $limit) {
                $writeSitemapFile($sitemap, $chunk, $sitemapIndex);
                $chunk++;
                $sitemap = Sitemap::create();
            }
        }

        $footerLinks = FooterMenu::all();
        foreach ($footerLinks as $footerLink) {
            $sitemap->add(
                Url::create($footerLink->link)
                    ->setLastModificationDate(Carbon::yesterday())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                    ->setPriority(0.1)
            );

            if (count($sitemap->getTags()) >= $limit) {
                $writeSitemapFile($sitemap, $chunk, $sitemapIndex);
                $chunk++;
                $sitemap = Sitemap::create();
            }
        }

        if (count($sitemap->getTags()) > 0) {
            $writeSitemapFile($sitemap, $chunk, $sitemapIndex);
        }

        $sitemapIndex->writeToFile($this->store_path('sitemap.xml'));

        $this->info('Sitemap generated successfully');
    }

    private function store_path($path)
    {
        return base_path() . '/../' . $path;
    }
}