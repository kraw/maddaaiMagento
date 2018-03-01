<h1>Syrus Magento Maddaai </h1>
<h2>Il tema utilizzato e Maddaai Theme - Parent di Magento Luma</h2>

<h2>Elenco dei moduli sviluppati:</h2>
<li>Syrus Drc_PreOrder</li>
<li>Syrus_Bidrequest</li>
<li>Syrus_Deadline</li>
<li>Syrus_Deisableaccess</li>
<li>Syrus_Navigation</li>
<li>Syrus_Passwordrevocer</li>
<li>Syrus_Remotelogin</li>
<li>Syrus_Stores</li>
<li>Syrus_Valutationproducts</li>

<h3>Funzioni modulo Syrus Drc_PreOrder</h3>
<ul><li>Modifica del bottone “Add to cart” a “Pre-Order”</li>
<li>Gestione del pre-order: avviso ai consumatori quando il prodotto è disponibile </li>
<li>Gestione dei preorder all’interno del backend del singolo negozio ed a livello globalev
<li>Gestione fatture di preorder che diventano order </li>
<li>Possibilità di creare gruppi di prodotti in pre-order </li>
<li>
<ul>Notifiche via mail al cliente su:
<li>accettazione del pre-order e notifica del passaggio ad Order</li>
<li>notifica di pagamento e spedizione</li>
<li>notifica di fatturazione</li>
<li>Compatibilità multi-store</ul>
</li>
</ul>

<h2>Per installare il modulo: </h2>
<ul>
<li>Andare nella cartella app</li>
<li>Andare nella cartella code, se non esiste createla </li>
<li>Copiate la cartella Drc con il suo contenuto nella cartella code</li>
<li>Da terminale recarsi nella root del progetto magneto ed eseguire i seguenti comandi in sequenziale<br />
<code>php bin/magento setup:upgrade</code><br />
<code>php bin/magento cache:clean</code><br />
<code>php bin/magento setup:static-content:deploy</code></li>
<li>Se avete fatto tutto bene nell'area admin dovreste trovare la voce Bids sotto Sales nel menu</li>
<li>Per capire come ragiona il plugin guardare qua: http://docs.magentoatdrc.com/preorder/ </li>
</ul>

<h3>Commandi utili:</h3>
<code>php bin/magento cache:clean </code> #pulisce la cache di magento<br />
<code>php bin/magento setup:static-content:deploy </code>#compila il modulo<br />
<code>php bin/magento setup:upgrade</code> #installa il modulo<br />
<code>php bin/magento module:enable Drc_PreOrder</code> #abilita il modulo<br />
<code>php bin/magento module:disable Drc_PreOrder --clear-static-content</code> #disabilita il modulo e cancella i riferimenti sulla view<br /> 
<code>php bin/magento module:disable Drc_PreOrder</code> #disabilita il modulo<br />
<code>rm -rf var/generation var/cache</code> #cancella i file generati da magento<br />
<code>chmod -R 777 var/* </code>#imposta i permessi coretti per la cartella var<br />
<code>chmod -R 777 pub/* </code>#imposta i permessi coretti per la cartella pub<br />

<h3>Aggiornamenti Magneto</h3>
<p>Per aggiornare la versione di Magento eseguire la seguente sequenza di commandi</p>
<code>composer require magento/product-community-edition 2.0.2 --no-update<br />
composer update<br />
rm -rf var/di var/generation<br>
php bin/magento cache:clean<br />
php bin/magento cache:flush<br />
php bin/magento setup:upgrade<br />
php bin/magento setup:di:compile<br />
php bin/magento indexer:reindex<br />
</code>

