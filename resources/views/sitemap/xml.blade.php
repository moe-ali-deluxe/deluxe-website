{{-- resources/views/sitemap/xml.blade.php --}}
{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($urls as $u)
  <url>
    <loc>{{ htmlspecialchars($u['loc'], ENT_XML1) }}</loc>
    @if(!empty($u['lastmod']))
      <lastmod>{{ \Illuminate\Support\Carbon::parse($u['lastmod'])->toAtomString() }}</lastmod>
    @endif
    @if(!empty($u['changefreq']))
      <changefreq>{{ $u['changefreq'] }}</changefreq>
    @endif
    @if(!empty($u['priority']))
      <priority>{{ $u['priority'] }}</priority>
    @endif
  </url>
@endforeach
</urlset>
