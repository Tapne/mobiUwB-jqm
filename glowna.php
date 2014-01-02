<?php
require_once './libs/PHPTAL.php';

session_start();
$template = new PHPTAL('template_index.html');
$template->setOutputMode(PHPTAL::HTML5);

if (file_exists('config.xml')) {
    $config = simplexml_load_file('config.xml');	// wczytanie pliku konfiguracyjnego
		
	// ---------------- AUTORZY ----------------
	
	// klasa Autor
	class Autor {
		public $imie;
		
		function __construct($imie) {
			$this->imie = $imie.' (autor)';
		}
	}

	// tworzymy tablice obiektow
	$autorzy = array();
	
	foreach ($config->autorzy->autor as $autor) {
		$autorzy[] = new Autor($autor);
	}

	// $template->autorzy = $autorzy;

	// -------------------------------------------
	
	// --------------- OPIEKUNOWIE ---------------
	
	class Opiekun extends Autor{
		//public $imie;

		function __construct($imie) {
			$this->imie = $imie.' (opiekun, autor)';
		}
	}
	
	// tworzymy tablice obiektow
	$opiekun = array();
	
	foreach ($config->opiekun as $opiekun) {
		$opiekunowie[] = new Opiekun($opiekun);
	}

	//$template->opiekunowie = $opiekunowie;
	
	// -------------------------------------------
	
	// ------------------ PLANY ------------------
	
	class Plan {
		public $link;
		public $opis;
		
		function __construct($link, $opis) {
			$this->link = $link;
			$this->opis = $opis;
		}
	}

	// tworzymy tablice obiektow
	$plany_Ist = array();
	
	foreach ($config->jednostka[0]->plany->pierwszy_stopien->plan as $plan_Ist) {
		$plany_Ist[] = new Plan($plan_Ist->link, $plan_Ist->opis);
	}
	
	
	$plany_IIst = array();
	
	foreach ($config->jednostka[0]->plany->drugi_stopien->plan as $plan_IIst) {
		$plany_IIst[] = new Plan($plan_IIst->link, $plan_IIst->opis);
	}

	// $template->plany_Ist = $plany_Ist;
	// $template->plany_IIst = $plany_IIst;

	// -------------------------------------------
	
	
	// ----------------- SEKCJE -----------------
	
	class Sekcja {
		public $id_sekcji;
		public $tytul_sekcji;
		public $zawartosc;
		
		function __construct($id_sekcji, $tytul_sekcji, $zawartosc) {
			$this->id_sekcji = $id_sekcji;
			$this->tytul_sekcji = $tytul_sekcji;
			$this->zawartosc = $zawartosc;
		}
	}

	// tworzymy tablice obiektow
	$sekcje = array();
	
	foreach ($config->jednostka[0]->sekcje->sekcja as $sekcja) {
		if ($sekcja['licznik'] != 'tak'){
			$sekcje[] = new Sekcja($sekcja->id_sekcji, $sekcja->tytul_sekcji, "<a href=\"#sekcja?strona=$sekcja->id_sekcji\" class=$sekcja->id_sekcji>$sekcja->tytul_sekcji</a>");
		} else{
			$sekcje[] = new Sekcja($sekcja->id_sekcji, $sekcja->tytul_sekcji, "<a href=\"#sekcja?strona=$sekcja->id_sekcji\" class=$sekcja->id_sekcji>$sekcja->tytul_sekcji</a><span class=ui-li-count id=\"licznik_sz\">0</span>");
		}
	}

	// $template->sekcje = $sekcje;

	// -------------------------------------------	
	
	// ------------ SEKCJE STATYCZNE -------------
	
	class SekcjaStatyczna extends Sekcja{
		//public $id_sekcji;
		//public $tytul_sekcji;
		
		function __construct($id_sekcji, $tytul_sekcji) {
			$this->id_sekcji = $id_sekcji;
			$this->tytul_sekcji = $tytul_sekcji;
		}
	}

	// tworzymy tablice obiektow
	$sekcje_statyczne = array();
	
	foreach ($config->jednostka[0]->sekcje_statyczne->sekcja_statyczna as $sekcja_statyczna) {
			$sekcje_statyczne[] = new SekcjaStatyczna($sekcja_statyczna->id_sekcji, $sekcja_statyczna->tytul_sekcji);
	}

	// $template->sekcje_statyczne = $sekcje_statyczne;

	// -------------------------------------------	
	
	/* Wczytanie danych z pliku konfiguracyjnego */
	
	$template->opiekunowie = $opiekunowie;
	$template->autorzy = $autorzy;
	
	$template->plany_Ist = $plany_Ist;
	$template->plany_IIst = $plany_IIst;
	$template->sekcje = $sekcje;
	$template->sekcje_statyczne = $sekcje_statyczne;
	
	// --
	
	$template->nazwa = $config->jednostka[0]->nazwa;
	$template->pelny_tytul = $config->jednostka[0]->pelny_tytul;
	$template->logo = $config->jednostka[0]->logo;
	
	$template->apiUrl = $config->jednostka[0]->apiUrl;

// mapa
	$template->tytul = $config->jednostka[0]->mapa->tytul;
	$template->dlugosc = $config->jednostka[0]->mapa->wspolrzedne->dlugosc;
	$template->szerokosc = $config->jednostka[0]->mapa->wspolrzedne->szerokosc;
	
// adres
	$template->kod = $config->jednostka[0]->adres->kod;
	$template->miasto = $config->jednostka[0]->adres->miasto;
	$template->ulica = $config->jednostka[0]->adres->ulica;
	$template->numer = $config->jednostka[0]->adres->numer;
	
	$template->email = $config->jednostka[0]->email;
	
	$template->mailto = 'mailto:'.$config->jednostka[0]->email;
	
	$template->tel1 = $config->jednostka[0]->tel1;
	
	$tel = 'tel:';
	$template->tel1_ = $tel.$config->jednostka[0]->tel1;
	
	// tutaj dla odmiany nie łączę - dałem tel:${tel2} w template_index.html
	$template->tel2 = $config->jednostka[0]->tel2;
	$template->fax = $config->jednostka[0]->fax;
	
	
	/* Zawartość specyficzna dla platform - Android/przeglądarka */
	
			 /* - Wskaźnik stanu połączenia z siecią; nie wyświetlany w aplikacji
				- Przycisk "Główna"; nie wyświetlany w aplikacji */
				
	// isset powinno być szybsze niż porównywanie wartości.
	if (isset($_SESSION['client'])) {
		$template->offline = '';
		$template->glowna = '';
	}
	else {
		$template->offline =
			'        <!-- offline indicator - wskaźnik stanu połączenia z siecią (online/offline) -->
	<script src=offline.min.js></script>
	<link rel=stylesheet href=offline-indicator.css>';
		$template->glowna = '<a href=glowna.php data-icon=home data-iconpos=left>Główna</a>';
	}
	
	try {
		echo $template->execute();
	}
	catch (Exception $e){
		echo $e;
	}	
	
	
} else {
    exit('Nie mozna otworzyc pliku config.xml.');
}

?>
