<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Kategori_Produk;
use App\Models\Produk;
use DB;
use App\Models\Review;
use Illuminate\Http\Request;

class ProdukWebController extends Controller
{
    public function index(Request $request)
    {
        $nama_produk = $request->query('cari');

        if ($nama_produk != '') {
            $data['produk'] = Produk::with(['foto_produk', 'kategori', 'pelapak'])->where('nama_produk', 'like', '%' . $nama_produk . '%')->get();
        } else {
            $data['produk'] = Produk::with(['foto_produk', 'kategori', 'pelapak'])->get();
        }

        $data['kategori'] = Kategori_Produk::select('id_kategori_produk', 'nama_kategori')->get();
        return view('web/web_home', $data);
    }

    public function produk(Request $request)
    {
        $kategori = $request->query('kategori');
        $nama_produk = $request->query('cari');
        $order = $request->query('order');

        if ($kategori != '') {
            $data['produk'] = Produk::with(['foto_produk', 'kategori', 'pelapak'])->when($kategori != '', function ($query) use ($kategori) {
                $query->whereHas('kategori', function ($query) use ($kategori) {
                    $query->where('nama_kategori', $kategori != '' ? $kategori : '');
                });
            })->orderBy($order == 'laris' ? 'terjual' : DB::raw('harga_jual - (diskon / 100 * harga_jual)'), $order == 'high' ? ('DESC') : ($order == 'laris' ? ('DESC') : ('ASC')))->paginate(12);
            // })->orderBy('harga_jual', $order == 'high' ? 'DESC' : 'ASC')->orderBy('terjual', $order == 'laris' , 'DESC')->paginate(12);
        } else if ($nama_produk != '') {
            $data['produk'] = Produk::with(['foto_produk', 'kategori', 'pelapak'])->where('nama_produk', 'like', '%' . $nama_produk . '%')->orderBy(DB::raw('harga_jual - (diskon / 100 * harga_jual)'), $order == 'high' ? 'DESC' : 'ASC')->paginate(12);
        } else {
            $data['produk'] = Produk::with(['foto_produk', 'kategori', 'pelapak'])->paginate(12);
        }
        $data['kategori'] = Kategori_Produk::Select('id_kategori_produk', 'nama_kategori')->get();
        return view('web/web_produk', $data);
    }

    public function popular(Request $request)
    {
        $data['produk'] = Produk::with(['foto_produk', 'kategori', 'pelapak'])->orderBy('terjual', 'DESC')->paginate(12);

        $data['kategori'] = Kategori_Produk::Select('id_kategori_produk', 'nama_kategori')->get();
        return view('web/web_produk', $data);
    }

    public function produkId(Request $request, $slug)
    {
        $data['produk'] = Produk::with(['foto_produk', 'kategori', 'pelapak'])->where('slug', $slug)->first();
        $data['produk_pelapak'] = Produk::with(['foto_produk', 'kategori', 'pelapak'])->where('pelapak_id', $data['produk']->pelapak->id_pelapak)->get();

        $data['review'] = Review::with('konsumen')->where('produk_id', $data['produk']->id_produk)->paginate(2);
        $data['counts'] = $data['review']->total();


        if ($request->ajax()) {
            return view('web.load.paginate', $data);
        }

        return view('web/web_produk_detail', $data);
    }
}
