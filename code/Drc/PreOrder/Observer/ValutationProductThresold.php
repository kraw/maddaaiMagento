<?php

	namespace Drc\PreOrder\Observer;
	
	use \Magento\Framework\Event\ObserverInterface;
	class ValutationProductThresold implements ObserverInterface {
		
		protected $productRepository;
		protected $imageFactory;

		public function __construct(
					\Magento\Backend\Block\Template\Context $context,
					\Magento\Catalog\Model\ProductRepository $productRepository,	
					\Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory,
					\Magento\Catalog\Helper\ImageFactory $imageFactory
					) {
			$this->_context = $context;
			$this->productRepository = $productRepository;
			$this->imageFactory = $imageFactory;
			$this->userCollectionFactory = $userCollectionFactory;
		}


		
		public function execute(\Magento\Framework\Event\Observer $observer) {
			$product_id = $observer->getData('product_id');
			$base_url = $this->_context->getStoreManager()->getStore()->getBaseUrl();
			//recupero le email degli admin
			$userCollection = $this->userCollectionFactory->create();
			$temp = array();
			//itero sugli utenti filtrando il ruolo
			foreach($userCollection as $user) {
				$temp[] = $user->getEmail();	
			}
			$ret = $this->send($temp, $base_url, $product_id);

	
		}
		
	
		//function to send notification mail
		//credits to PaoloVox :)	
		public function send($emails,$base_url,$id_prodotto) {
			//getting product id from event
			//Initialize needed variables
		        $your_name = 'MADDAAI STORE';
        		$your_email = 'maddaai.store@gmail.com';
         		$your_password = 'danieledaniele';

         		//SMTP server configuration
         		$smtpHost = 'smtp.gmail.com';
         		$smtpConf = array(
          			'auth' => 'login',
          			'ssl' => 'ssl',
          			'port' => '465',
          			'username' => $your_email,
          			'password' => $your_password
         		);
         		$transport = new \Zend_Mail_Transport_Smtp($smtpHost, $smtpConf);


			//getting product info
			
			$prodotto = $this->productRepository->getById($id_prodotto);
			$url_image = $this->imageFactory->create()->init($prodotto, 'product_base_image')->getUrl();
     			$nome_prodotto = $prodotto->getName();
     			$prezzo =  $prodotto->getPrice();
     			$short_description = $prodotto->getShortDescription();

			//Create email
			$body = $this->getBody($url_image,$nome_prodotto,$prezzo,$short_description,$base_url);
		        $mail = new \Zend_Mail();
         		$mail->setFrom($your_email, $your_name);
			foreach($emails as $email) {
         			$mail->addTo($email);
			}
         		$mail->setSubject("Notifica Raggiungimento Quota di Like");
         		$mail->setBodyHtml($body);

         		//Send
         		$sent = true;
         		try {
          			$mail->send($transport);
         		}
         		catch (Exception $e) {
          			$sent = false;
                         	Zend_Debug::dump("errore email");
         		}
         		\Zend_Debug::dump("email mandata con successo");
         		//Return boolean indicating success or failure
        		return $sent;
		}
		
		public function getBody($img,$nome_prodotto,$prezzo,$short_description,$base_url){

      $space = "https://ci6.googleusercontent.com/proxy/K9UD7F7-BWO0aVw6xoSNz6TiDMctwkLffAggfmqRkObNkiRZxnZ25ryzdyAngQ5EPh_jT54VaJoLR7ydgNY7_M9V_iAKTrK1P5EjyGI9C6YZQMS2gxaJsEZYVVvTNml0mLcxkx_2WBT5GsQzWwq4YTkgjHc=s0-d-e1-ft#https://developer.magento.com/skin/frontend/rwd/developer_portal/images/emails/blank_email.gif";
	  $mess =  "Il seguente prodotto ha raggiunto il limite di like";
      $display =  "";

      $data = date('d/m/Y'); //date('d/m/Y H:i:s')
      return "<div style='font-size:12px;font-style:normal;font-variant-caps:normal;font-weight:normal;letter-spacing:normal;text-align:start;text-indent:0px;text-transform:none;white-space:normal;word-spacing:0px;background-color:rgb(246,246,246);font-family:Verdana,Arial,Helvetica,sans-serif;margin:0px;padding:0px'>
   <table class='m_-4013470537037416767pad_null' cellspacing='0' cellpadding='0' border='0' height='100%' width='100%' style='font-family:verdana,arial!important'>
      <tbody>
         <tr>
            <td style='font-family:verdana,arial!important'><img src='".$space."' alt='' width='1' height='10' border='0' style='display:block' class='CToWUd'></td>
         </tr>
         <tr>
            <td align='center' valign='top' style='font-family:verdana,arial!important;padding:0px'>
               <table class='m_-4013470537037416767pad_null' bgcolor='FAFBFA' cellspacing='0' cellpadding='0' border='0' width='650' style='font-family:verdana,arial!important;border-width:4px 1px 1px;border-style:solid;border-color:rgb(239,103,47) rgb(224,224,224) rgb(224,224,224)'>
                  <tbody>
                     <tr>
                        <td valign='top' style='font-family:verdana,arial!important'>
                           <table class='m_-4013470537037416767pad_null' cellspacing='0' cellpadding='0' width='100%' bgcolor='#ffffff' style='font-family:verdana,arial!important;border-collapse:collapse'>
                              <tbody>
                                 <tr>
                                    <td colspan='3' style='font-family:verdana,arial!important'><img src='".$space."' alt='' width='1' height='20' border='0' style='display:block' class='CToWUd'></td>
                                 </tr>
                                 <tr>
                                    <td width='20' style='font-family:verdana,arial!important'><img src='".$space."' alt='' width='20' height='1' border='0' style='display:block' class='CToWUd'></td>
                                    <td style='font-family:verdana,arial!important'><a href='http://mail.magento.com/wf/click?upn=KOJzixp5uSEAZuqcF59y1CT8PwoBV2wT5-2B093BameAmZY8YtEfQXsbNNFQom1imV_gJ5ORFI-2BLCaunNoeQsLPaWHt1cio8iG51tRLS-2FGjyH9O1JAd8nWFz5U6KrtwoktoI2FOqq7FPb1c02ruJScJI-2F12SZWX0jPieMnPsyJdAhK2UZQkPj7H-2FT5cWgI6UYVb-2Bwl8rWDRzQ-2FWjT05dpjm5EyJeU40UEJJMTdl0Lscss7roNcwGzCkQI5DNNwdkrzPpeduCJlODm4Eihy-2FPZUSPhiNmHeDumxhxxoRUlNL744-3D' style='text-decoration:none' target='_blank' data-saferedirecturl='https://www.google.com/url?hl=it&amp;q=http://mail.magento.com/wf/click?upn%3DKOJzixp5uSEAZuqcF59y1CT8PwoBV2wT5-2B093BameAmZY8YtEfQXsbNNFQom1imV_gJ5ORFI-2BLCaunNoeQsLPaWHt1cio8iG51tRLS-2FGjyH9O1JAd8nWFz5U6KrtwoktoI2FOqq7FPb1c02ruJScJI-2F12SZWX0jPieMnPsyJdAhK2UZQkPj7H-2FT5cWgI6UYVb-2Bwl8rWDRzQ-2FWjT05dpjm5EyJeU40UEJJMTdl0Lscss7roNcwGzCkQI5DNNwdkrzPpeduCJlODm4Eihy-2FPZUSPhiNmHeDumxhxxoRUlNL744-3D&amp;source=gmail&amp;ust=1492954961054000&amp;usg=AFQjCNFME_rM1GnisKOtQ8foOraYGje4Cw'><img src='http://magento.maddaai.it/pub/media/logo/stores/1/maddaai-logo.png' width='150' alt='Magento Marketplace' border='0' class='CToWUd'></a></td>
                                    <td width='20' style='font-family:verdana,arial!important'><img src='".$space."' alt='' width='20' height='1' border='0' style='display:block' class='CToWUd'></td>
                                 </tr>
                                 <tr>
                                    <td colspan='3' style='font-family:verdana,arial!important'><img src='".$space."' alt='' width='1' height='20' border='0' style='display:block' class='CToWUd'></td>
                                 </tr>
                              </tbody>
                           </table>
                        </td>
                     </tr>
                     <tr>
                        <td valign='top' style='font-family:verdana,arial!important'>
                           <table class='m_-4013470537037416767pad_null' cellspacing='0' cellpadding='0' width='100%' style='font-family:verdana,arial!important;border-collapse:collapse'>
                              <tbody>
                                 <tr>
                                    <td colspan='3' style='font-family:verdana,arial!important'><img src='".$space."' alt='' width='1' height='40' border='0' style='display:block' class='CToWUd'></td>
                                 </tr>
                                 <tr>
                                    <td width='20' style='font-family:verdana,arial!important'><img src='".$space."' alt='' width='20' height='1' border='0' style='display:block' class='CToWUd'></td>
                                    <td style='font-family:verdana,arial!important'>
                                       <table class='m_-4013470537037416767pad_null' cellspacing='0' cellpadding='0' width='100%' style='font-family:verdana,arial!important;border-collapse:collapse'>
                                          <tbody>
                                             <tr>
                                                <td valign='top' align='center' style='font-family:verdana,arial!important;text-align:center'>
                                                   <div style='font-family:Arial,helvetica,sans-serif;color:rgb(18,18,18);font-size:17px;line-height:36px;margin:0px;padding:0px;margin-top:-70px'>Ciao </div>
                                                </td>
                                             </tr>
                                             <tr>
                                                <td valign='top' align='center' style='font-family:verdana,arial!important;text-align:center'>
                                                   <div style='font-family:Arial,helvetica,sans-serif;color:rgb(18,18,18);font-size:24px;line-height:24px;margin:0px;padding:0px'>".$mess."</div>
                                                </td>
                                             </tr>
                                             <tr>
                                                <td style='font-family:verdana,arial!important'><img src='https://ci6.googleusercontent.com/proxy/K9UD7F7-BWO0aVw6xoSNz6TiDMctwkLffAggfmqRkObNkiRZxnZ25ryzdyAngQ5EPh_jT54VaJoLR7ydgNY7_M9V_iAKTrK1P5EjyGI9C6YZQMS2gxaJsEZYVVvTNml0mLcxkx_2WBT5GsQzWwq4YTkgjHc=s0-d-e1-ft#https://developer.magento.com/skin/frontend/rwd/developer_portal/images/emails/blank_email.gif' alt='' width='1' height='30' border='0' style='display:block' class='CToWUd'></td>
                                             </tr>
                                             <tr>
                                                <td valign='top' align='center' style='font-family:verdana,arial!important;text-align:center'>
                                                   <div style='font-family:Arial,helvetica,sans-serif;color:rgb(18,18,18);font-size:15px;line-height:20px;margin:0px;padding:0px'><span style='".$display."; color:rgb(152,152,152);font-size:15px;line-height:20px;margin:0px;padding:0px;font-family:Arial,helvetica,sans-serif'></span><span style='".$display."' class='m_-4013470537037416767Apple-converted-space'>&nbsp;</span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='m_-4013470537037416767Apple-converted-space' style='".$display."'>&nbsp;</span><span style='color:rgb(152,152,152);font-size:15px;line-height:20px;margin:0px;padding:0px;font-family:Arial,helvetica,sans-serif'>Data:</span><span class='m_-4013470537037416767Apple-converted-space'>&nbsp;</span>".$data."</div>
                                                </td>
                                             </tr>
                                             <tr>
                                                <td style='font-family:verdana,arial!important'><img src='".$space."' alt='' width='1' height='30' border='0' style='display:block' class='CToWUd'></td>
                                             </tr>
                                             <tr>
                                                <td valign='top' style='font-family:verdana,arial!important'>
                                                   <table class='m_-4013470537037416767pad_null' cellspacing='0' cellpadding='0' width='600' bgcolor='#ffffff' align='center' style='font-family:verdana,arial!important;border-collapse:collapse'>
                                                      <tbody>
                                                         <tr>
                                                            <td width='25' style='font-family:verdana,arial!important'><img src='https://ci6.googleusercontent.com/proxy/K9UD7F7-BWO0aVw6xoSNz6TiDMctwkLffAggfmqRkObNkiRZxnZ25ryzdyAngQ5EPh_jT54VaJoLR7ydgNY7_M9V_iAKTrK1P5EjyGI9C6YZQMS2gxaJsEZYVVvTNml0mLcxkx_2WBT5GsQzWwq4YTkgjHc=s0-d-e1-ft#https://developer.magento.com/skin/frontend/rwd/developer_portal/images/emails/blank_email.gif' alt='' width='25' height='1' border='0' style='display:block' class='CToWUd'></td>
                                                            <td valign='top' width='550' style='font-family:Arial,helvetica,sans-serif;font-weight:normal;font-size:14px;line-height:20px;color:rgb(49,48,43)'>
                                                               <table class='m_-4013470537037416767pad_null' cellspacing='0' cellpadding='0' width='550' style='font-family:verdana,arial!important;border-collapse:collapse'>
                                                                  <tbody>
                                                                     <tr>
                                                                        <td style='font-family:verdana,arial!important'><img src='".$space."' alt='' width='1' height='30' border='0' style='display:block' class='CToWUd'></td>
                                                                     </tr>
                                                                     <tr>
                                                                        <td valign='top' style='font-family:verdana,arial!important'>
                                                                           <div style='font-family:Arial,helvetica,sans-serif;color:rgb(18,18,18);font-size:24px;line-height:24px;margin:0px;padding:0px'>Sommario</div>
                                                                        </td>
                                                                     </tr>
                                                                     <tr>
                                                                        <td style='font-family:verdana,arial!important'><img src='https://ci6.googleusercontent.com/proxy/K9UD7F7-BWO0aVw6xoSNz6TiDMctwkLffAggfmqRkObNkiRZxnZ25ryzdyAngQ5EPh_jT54VaJoLR7ydgNY7_M9V_iAKTrK1P5EjyGI9C6YZQMS2gxaJsEZYVVvTNml0mLcxkx_2WBT5GsQzWwq4YTkgjHc=s0-d-e1-ft#https://developer.magento.com/skin/frontend/rwd/developer_portal/images/emails/blank_email.gif' alt='' width='1' height='30' border='0' style='display:block' class='CToWUd'></td>
                                                                     </tr>
                                                                     <tr>
                                                                        <td style='font-family:verdana,arial!important'>
                                                                           <table class='m_-4013470537037416767pad_null' cellspacing='0' cellpadding='0' width='100%' style='font-family:verdana,arial!important;border-collapse:collapse'>
                                                                              <tbody>
                                                                                 <tr>
                                                                                    <td style='font-family:verdana,arial!important'>
                                                                                       <table class='m_-4013470537037416767pad_null' cellspacing='0' cellpadding='0' width='100%' style='font-family:verdana,arial!important;border-collapse:collapse'>
                                                                                          <tbody>
                                                                                             <tr>
                                                                                                <td colspan='2' style='font-family:verdana,arial!important'><img src='".$space."' alt='' width='1' height='30' border='0' style='display:block' class='CToWUd'></td>
                                                                                             </tr>
                                                                                             <tr>
                                                                                                <td align='left' width='160' valign='top' style='font-family:verdana,arial!important;text-align:left;width:160px'><a href='' target='_blank'>
                                                                                                <img src='".$img."' alt='' width='148' border='0' style='display:block' class='CToWUd'></a></td>
                                                                                                <td style='font-family:verdana,arial!important'>
                                                                                                   <table class='m_-4013470537037416767pad_null' cellspacing='0' cellpadding='0' width='100%' style='font-family:verdana,arial!important;border-collapse:collapse'>
                                                                                                      <tbody>
                                                                                                         <tr>
                                                                                                            <td valign='top' style='font-family:verdana,arial!important'>
                                                                                                               <p style='font-family:Arial,helvetica,sans-serif;color:rgb(95,95,95);font-size:14px;font-weight:bolder'>".$nome_prodotto."</p>
                                                                                                               <p style='display:none; font-family:Arial,helvetica,sans-serif;color:rgb(18,18,18);font-weight:normal;font-size:14px;line-height:24px'>".$short_description."</p>
                                                                                                               <p style='display:none; font-family:Arial,helvetica,sans-serif;color:rgb(18,18,18);font-weight:normal;font-size:14px;line-height:24px'><span style='color:rgb(150,150,150);font-family:Arial,helvetica,sans-serif;font-weight:normal;font-size:14px;line-height:24px'>Component name:</span><span class='m_-4013470537037416767Apple-converted-space'>&nbsp;</span>aheadworks/module-<wbr>rbslider</p>
                                                                                                               <p style='display:none; font-family:Arial,helvetica,sans-serif;color:rgb(18,18,18);font-weight:normal;font-size:14px;line-height:24px'><span style='color:rgb(150,150,150);font-family:Arial,helvetica,sans-serif;font-weight:normal;font-size:14px;line-height:24px'>Component version:</span><span class='m_-4013470537037416767Apple-converted-space'>&nbsp;</span>1.1.1</p>
                                                                                                               <p style='display:none; font-family:Arial,helvetica,sans-serif;color:rgb(18,18,18);font-weight:normal;font-size:14px;line-height:24px'><span style='color:rgb(150,150,150);font-family:Arial,helvetica,sans-serif;font-weight:normal;font-size:14px;line-height:24px'>Developer:</span><span class='m_-4013470537037416767Apple-converted-space'>&nbsp;</span>Aheadworks</p>
                                                                                                               <p style='display:none; font-family:Arial,helvetica,sans-serif;color:rgb(18,18,18);font-weight:normal;font-size:14px;line-height:24px'><span style='color:rgb(150,150,150);font-family:Arial,helvetica,sans-serif;font-weight:normal;font-size:14px;line-height:24px'>Platform:</span><span class='m_-4013470537037416767Apple-converted-space'>&nbsp;</span>Magento 2 Community Edition</p>
                                                                                                            </td>
                                                                                                            <td valign='top' width='100' align='right' style='font-family:verdana,arial!important;text-align:right'><span style='color:rgb(150,150,150);font-family:Arial,helvetica,sans-serif;font-weight:normal;font-size:14px;line-height:24px'> x<span class='m_-4013470537037416767Apple-converted-space'>&nbsp;</span><span class='m_-4013470537037416767price'></span></span></td>
                                                                                                         </tr>
                                                                                                         <tr>
                                                                                                            <td valign='top' colspan='2' align='right' style='font-family:verdana,arial!important;text-align:right'><img src='".$space."' alt='' width='1' height='20' border='0' style='display:block' class='CToWUd'></td>
                                                                                                         </tr>
                                                                                                         <tr style='display:none'>
                                                                                                            <td valign='top' colspan='2' align='right' style='font-family:verdana,arial!important;text-align:right'><span style='color:rgb(18,18,18);font-family:Arial,helvetica,sans-serif;font-weight:normal;font-size:24px;line-height:24px'><span class='m_-4013470537037416767price'>$0.00</span></span></td>
                                                                                                         </tr>
                                                                                                      </tbody>
                                                                                                   </table>
                                                                                                </td>
                                                                                             </tr>
                                                                                             <tr style='border-bottom-width:1px;border-bottom-style:solid;border-bottom-color:rgb(204,204,204)'>
                                                                                                <td colspan='2' style='font-family:verdana,arial!important'><img src='https://ci5.googleusercontent.com/proxy/T5pxbfRwhxiWmT_RCeYdM4Nv3gCf8s8bd4OvuthUQgty88sad-mW8R0CAKINgcPZ-HMMapC_pWKp3IvTqjiC2nKNTTAgNqAojNDpR2pNLtv5pqIsl-mQI8TzReH1ehSF1lmcTu4f3Cqq29ycSr73TIO_ngcuCg=s0-d-e1-ft#https://marketplace.magento.com/skin/frontend/rwd/developer_portal/images/emails/blank_email.gif' alt='' width='1' height='30' border='0' style='display:block' class='CToWUd'></td>
                                                                                             </tr>
                                                                                          </tbody>
                                                                                       </table>
                                                                                    </td>
                                                                                 </tr>
                                                                                 <tr>
                                                                                    <td colspan='2' style='font-family:verdana,arial!important'><img src='https://ci5.googleusercontent.com/proxy/T5pxbfRwhxiWmT_RCeYdM4Nv3gCf8s8bd4OvuthUQgty88sad-mW8R0CAKINgcPZ-HMMapC_pWKp3IvTqjiC2nKNTTAgNqAojNDpR2pNLtv5pqIsl-mQI8TzReH1ehSF1lmcTu4f3Cqq29ycSr73TIO_ngcuCg=s0-d-e1-ft#https://marketplace.magento.com/skin/frontend/rwd/developer_portal/images/emails/blank_email.gif' alt='' width='1' height='30' border='0' style='display:block' class='CToWUd'></td>
                                                                                 </tr>
                                                                                 
                                                                                 <tr style='border-bottom-width:1px;border-bottom-style:solid;border-bottom-color:rgb(204,204,204)'>
                                                                                    <td colspan='2' style='font-family:verdana,arial!important'><img src='https://ci5.googleusercontent.com/proxy/T5pxbfRwhxiWmT_RCeYdM4Nv3gCf8s8bd4OvuthUQgty88sad-mW8R0CAKINgcPZ-HMMapC_pWKp3IvTqjiC2nKNTTAgNqAojNDpR2pNLtv5pqIsl-mQI8TzReH1ehSF1lmcTu4f3Cqq29ycSr73TIO_ngcuCg=s0-d-e1-ft#https://marketplace.magento.com/skin/frontend/rwd/developer_portal/images/emails/blank_email.gif' alt='' width='1' height='30' border='0' style='display:block' class='CToWUd'></td>
                                                                                 </tr>
                                                                                 <tr style='".$display."'>
                                                                                    <td colspan='2' style='font-family:verdana,arial!important'><img src='https://ci5.googleusercontent.com/proxy/T5pxbfRwhxiWmT_RCeYdM4Nv3gCf8s8bd4OvuthUQgty88sad-mW8R0CAKINgcPZ-HMMapC_pWKp3IvTqjiC2nKNTTAgNqAojNDpR2pNLtv5pqIsl-mQI8TzReH1ehSF1lmcTu4f3Cqq29ycSr73TIO_ngcuCg=s0-d-e1-ft#https://marketplace.magento.com/skin/frontend/rwd/developer_portal/images/emails/blank_email.gif' alt='' width='1' height='30' border='0' style='display:block' class='CToWUd'></td>
                                                                                 </tr>
                                                                              </tbody>
                                                                           </table>
                                                                           <table style='display:none' class='m_-4013470537037416767pad_null' cellspacing='0' cellpadding='0' width='100%' style='font-family:verdana,arial!important;border-collapse:collapse'>
                                                                              <tbody>
                                                                                 <tr>
                                                                                    <td valign='top' style='font-family:verdana,arial!important'>
                                                                                       <div style='font-family:Arial,helvetica,sans-serif;color:rgb(18,18,18);font-size:15px;line-height:20px;margin:0px;padding:0px'><strong style='font-family:Arial,helvetica,sans-serif;color:rgb(18,18,18);font-size:15px;line-height:20px;margin:0px;padding:0px'>Billing Information</strong></div>
                                                                                       <img src='https://ci5.googleusercontent.com/proxy/T5pxbfRwhxiWmT_RCeYdM4Nv3gCf8s8bd4OvuthUQgty88sad-mW8R0CAKINgcPZ-HMMapC_pWKp3IvTqjiC2nKNTTAgNqAojNDpR2pNLtv5pqIsl-mQI8TzReH1ehSF1lmcTu4f3Cqq29ycSr73TIO_ngcuCg=s0-d-e1-ft#https://marketplace.magento.com/skin/frontend/rwd/developer_portal/images/emails/blank_email.gif' alt='' width='1' height='20' border='0' style='display:block' class='CToWUd'>
                                                                                       <div style='font-family:Arial,helvetica,sans-serif;color:rgb(18,18,18);font-size:15px;line-height:20px;margin:0px;padding:0px'><a href='mailto:maddaai.store@gmail.com' style='color:rgb(13,150,197);text-decoration:none' target='_blank'>maddaai.store@gmail.com</a></div>
                                                                                       <div style='font-family:Arial,helvetica,sans-serif;color:rgb(18,18,18);font-size:15px;line-height:20px;margin:0px;padding:0px'>maddaai</div>
                                                                                       <div style='font-family:Arial,helvetica,sans-serif;color:rgb(18,18,18);font-size:15px;line-height:20px;margin:0px;padding:0px'>Lazio, IT 00198</div>
                                                                                    </td>
                                                                                    <td width='50' style='font-family:verdana,arial!important'><img src='https://ci5.googleusercontent.com/proxy/T5pxbfRwhxiWmT_RCeYdM4Nv3gCf8s8bd4OvuthUQgty88sad-mW8R0CAKINgcPZ-HMMapC_pWKp3IvTqjiC2nKNTTAgNqAojNDpR2pNLtv5pqIsl-mQI8TzReH1ehSF1lmcTu4f3Cqq29ycSr73TIO_ngcuCg=s0-d-e1-ft#https://marketplace.magento.com/skin/frontend/rwd/developer_portal/images/emails/blank_email.gif' alt='' width='50' height='1' border='0' style='display:block' class='CToWUd'></td>
                                                                                    <td width='200' valign='top' style='font-family:verdana,arial!important'></td>
                                                                                 </tr>
                                                                                 <tr>
                                                                                    <td colspan='3' style='font-family:verdana,arial!important'><img src='https://ci5.googleusercontent.com/proxy/T5pxbfRwhxiWmT_RCeYdM4Nv3gCf8s8bd4OvuthUQgty88sad-mW8R0CAKINgcPZ-HMMapC_pWKp3IvTqjiC2nKNTTAgNqAojNDpR2pNLtv5pqIsl-mQI8TzReH1ehSF1lmcTu4f3Cqq29ycSr73TIO_ngcuCg=s0-d-e1-ft#https://marketplace.magento.com/skin/frontend/rwd/developer_portal/images/emails/blank_email.gif' alt='' width='1' height='30' border='0' style='display:block' class='CToWUd'></td>
                                                                                 </tr>
                                                                              </tbody>
                                                                           </table>
                                                                        </td>
                                                                     </tr>
                                                                     <tr>
                                                                        <td style='font-family:verdana,arial!important'><img src='https://ci6.googleusercontent.com/proxy/K9UD7F7-BWO0aVw6xoSNz6TiDMctwkLffAggfmqRkObNkiRZxnZ25ryzdyAngQ5EPh_jT54VaJoLR7ydgNY7_M9V_iAKTrK1P5EjyGI9C6YZQMS2gxaJsEZYVVvTNml0mLcxkx_2WBT5GsQzWwq4YTkgjHc=s0-d-e1-ft#https://developer.magento.com/skin/frontend/rwd/developer_portal/images/emails/blank_email.gif' alt='' width='1' height='20' border='0' style='display:block' class='CToWUd'></td>
                                                                     </tr>
                                                                  </tbody>
                                                               </table>
                                                            </td>
                                                            <td width='25' style='font-family:verdana,arial!important'><img src='https://ci6.googleusercontent.com/proxy/K9UD7F7-BWO0aVw6xoSNz6TiDMctwkLffAggfmqRkObNkiRZxnZ25ryzdyAngQ5EPh_jT54VaJoLR7ydgNY7_M9V_iAKTrK1P5EjyGI9C6YZQMS2gxaJsEZYVVvTNml0mLcxkx_2WBT5GsQzWwq4YTkgjHc=s0-d-e1-ft#https://developer.magento.com/skin/frontend/rwd/developer_portal/images/emails/blank_email.gif' alt='' width='25' height='1' border='0' style='display:block' class='CToWUd'></td>
                                                         </tr>
                                                      </tbody>
                                                   </table>
                                                </td>
                                             </tr>
                                             <tr style='display:none'>
                                                <td style='font-family:verdana,arial!important'><img src='https://ci6.googleusercontent.com/proxy/K9UD7F7-BWO0aVw6xoSNz6TiDMctwkLffAggfmqRkObNkiRZxnZ25ryzdyAngQ5EPh_jT54VaJoLR7ydgNY7_M9V_iAKTrK1P5EjyGI9C6YZQMS2gxaJsEZYVVvTNml0mLcxkx_2WBT5GsQzWwq4YTkgjHc=s0-d-e1-ft#https://developer.magento.com/skin/frontend/rwd/developer_portal/images/emails/blank_email.gif' alt='' width='1' height='30' border='0' style='display:block' class='CToWUd'></td>
                                             </tr>
                                             <tr>
                                                <td valign='top' style='font-family:verdana,arial!important'>
                                                   <div style='font-family:Arial,helvetica,sans-serif;color:rgb(18,18,18);font-size:15px;line-height:20px;margin:0px;padding:0px'><strong style='font-family:Arial,helvetica,sans-serif;color:rgb(18,18,18);font-size:15px;line-height:20px;margin:0px;padding:0px'>Hai bisogno d'aiuto?</strong></div>
                                                </td>
                                             </tr>
                                             <tr>
                                                <td style='font-family:verdana,arial!important'><img src='https://ci6.googleusercontent.com/proxy/K9UD7F7-BWO0aVw6xoSNz6TiDMctwkLffAggfmqRkObNkiRZxnZ25ryzdyAngQ5EPh_jT54VaJoLR7ydgNY7_M9V_iAKTrK1P5EjyGI9C6YZQMS2gxaJsEZYVVvTNml0mLcxkx_2WBT5GsQzWwq4YTkgjHc=s0-d-e1-ft#https://developer.magento.com/skin/frontend/rwd/developer_portal/images/emails/blank_email.gif' alt='' width='1' height='30' border='0' style='display:block' class='CToWUd'></td>
                                             </tr>
                                             <tr style='display:none'>
                                                <td valign='top' style='font-family:verdana,arial!important'>
                                                   <div style='font-family:Arial,helvetica,sans-serif;color:rgb(18,18,18);font-size:15px;line-height:20px;margin:0px;padding:0px'>1. Connect your purchases to your Magento 2 platform by using your<a href=''>authorization key access</a></div>
                                                </td>
                                             </tr>
                                             <tr style='display:none'>
                                                <td style='font-family:verdana,arial!important'><img src='https://ci6.googleusercontent.com/proxy/K9UD7F7-BWO0aVw6xoSNz6TiDMctwkLffAggfmqRkObNkiRZxnZ25ryzdyAngQ5EPh_jT54VaJoLR7ydgNY7_M9V_iAKTrK1P5EjyGI9C6YZQMS2gxaJsEZYVVvTNml0mLcxkx_2WBT5GsQzWwq4YTkgjHc=s0-d-e1-ft#https://developer.magento.com/skin/frontend/rwd/developer_portal/images/emails/blank_email.gif' alt='' width='1' height='30' border='0' style='display:block' class='CToWUd'></td>
                                             </tr>
                                             <tr style='display:none'>
                                                <td valign='top' style='font-family:verdana,arial!important'>
                                                   <div style='font-family:Arial,helvetica,sans-serif;color:rgb(18,18,18);font-size:15px;line-height:20px;margin:0px;padding:0px'>2. Learn how to use the<span class='m_-4013470537037416767Apple-converted-space'>&nbsp;</span><a href=''>Component Manager</a><span class='m_-4013470537037416767Apple-converted-space'>&nbsp;</span>to install newly purchased extensions.</div>
                                                </td>
                                             </tr>
                                             <tr style='display:none'>
                                                <td style='font-family:verdana,arial!important'><img src='https://ci6.googleusercontent.com/proxy/K9UD7F7-BWO0aVw6xoSNz6TiDMctwkLffAggfmqRkObNkiRZxnZ25ryzdyAngQ5EPh_jT54VaJoLR7ydgNY7_M9V_iAKTrK1P5EjyGI9C6YZQMS2gxaJsEZYVVvTNml0mLcxkx_2WBT5GsQzWwq4YTkgjHc=s0-d-e1-ft#https://developer.magento.com/skin/frontend/rwd/developer_portal/images/emails/blank_email.gif' alt='' width='1' height='30' border='0' style='display:block' class='CToWUd'></td>
                                             </tr>
                                             <tr>
                                                <td valign='top' style='font-family:verdana,arial!important'>
                                                   <div style='font-family:Arial,helvetica,sans-serif;color:rgb(18,18,18);font-size:15px;line-height:20px;margin:0px;padding:0px'>Cottattaci su<span class='m_-4013470537037416767Apple-converted-space'>&nbsp;</span><a href='mailto:maddaai.store@ŋmail.com.com' style='color:rgb(13,150,197);text-decoration:none' target='_blank'>maddaai.store@gmail.com</a></div>
                                                </td>
                                             </tr>
                                             <tr></tr>
                                             <tr style='display:none'>
                                                <td style='font-family:verdana,arial!important'><img src='https://ci6.googleusercontent.com/proxy/K9UD7F7-BWO0aVw6xoSNz6TiDMctwkLffAggfmqRkObNkiRZxnZ25ryzdyAngQ5EPh_jT54VaJoLR7ydgNY7_M9V_iAKTrK1P5EjyGI9C6YZQMS2gxaJsEZYVVvTNml0mLcxkx_2WBT5GsQzWwq4YTkgjHc=s0-d-e1-ft#https://developer.magento.com/skin/frontend/rwd/developer_portal/images/emails/blank_email.gif' alt='' width='1' height='30' border='0' style='display:block' class='CToWUd'></td>
                                             </tr>
                                             <tr style='display:none'>
                                                <td valign='top' style='font-family:verdana,arial!important'>
                                                   <div style='font-family:Arial,helvetica,sans-serif;color:rgb(18,18,18);font-size:15px;line-height:20px;margin:0px;padding:0px'>Note: Magento does not test extension interoperability. Be sure to test this extension in your environment along with any customization.</div>
                                                </td>
                                             </tr>
                                             <tr style='display:none'>
                                                <td style='font-family:verdana,arial!important'><img src='https://ci6.googleusercontent.com/proxy/K9UD7F7-BWO0aVw6xoSNz6TiDMctwkLffAggfmqRkObNkiRZxnZ25ryzdyAngQ5EPh_jT54VaJoLR7ydgNY7_M9V_iAKTrK1P5EjyGI9C6YZQMS2gxaJsEZYVVvTNml0mLcxkx_2WBT5GsQzWwq4YTkgjHc=s0-d-e1-ft#https://developer.magento.com/skin/frontend/rwd/developer_portal/images/emails/blank_email.gif' alt='' width='1' height='30' border='0' style='display:block' class='CToWUd'></td>
                                             </tr>
                                          </tbody>
                                       </table>
                                    </td>
                                    <td width='20' style='font-family:verdana,arial!important'><img src='https://ci6.googleusercontent.com/proxy/K9UD7F7-BWO0aVw6xoSNz6TiDMctwkLffAggfmqRkObNkiRZxnZ25ryzdyAngQ5EPh_jT54VaJoLR7ydgNY7_M9V_iAKTrK1P5EjyGI9C6YZQMS2gxaJsEZYVVvTNml0mLcxkx_2WBT5GsQzWwq4YTkgjHc=s0-d-e1-ft#https://developer.magento.com/skin/frontend/rwd/developer_portal/images/emails/blank_email.gif' alt='' width='20' height='1' border='0' style='display:block' class='CToWUd'></td>
                                 </tr>
                                 <tr>
                                    <td colspan='3' style='font-family:verdana,arial!important'><img src='https://ci6.googleusercontent.com/proxy/K9UD7F7-BWO0aVw6xoSNz6TiDMctwkLffAggfmqRkObNkiRZxnZ25ryzdyAngQ5EPh_jT54VaJoLR7ydgNY7_M9V_iAKTrK1P5EjyGI9C6YZQMS2gxaJsEZYVVvTNml0mLcxkx_2WBT5GsQzWwq4YTkgjHc=s0-d-e1-ft#https://developer.magento.com/skin/frontend/rwd/developer_portal/images/emails/blank_email.gif' alt='' width='1' height='40' border='0' style='display:block' class='CToWUd'></td>
                                 </tr>
                              </tbody>
                           </table>
                        </td>
                     </tr>
                     <tr>
                        <td bgcolor='#31302b' style='font-family:verdana,arial!important;background-color:rgb(49,48,43);background-position:initial initial;background-repeat:initial initial'>
                           <table class='m_-4013470537037416767pad_null' cellspacing='0' cellpadding='0' width='100%' style='font-family:verdana,arial!important;border-collapse:collapse'>
                              <tbody>
                                 <tr>
                                    <td colspan='3' style='font-family:verdana,arial!important'><img src='https://ci6.googleusercontent.com/proxy/K9UD7F7-BWO0aVw6xoSNz6TiDMctwkLffAggfmqRkObNkiRZxnZ25ryzdyAngQ5EPh_jT54VaJoLR7ydgNY7_M9V_iAKTrK1P5EjyGI9C6YZQMS2gxaJsEZYVVvTNml0mLcxkx_2WBT5GsQzWwq4YTkgjHc=s0-d-e1-ft#https://developer.magento.com/skin/frontend/rwd/developer_portal/images/emails/blank_email.gif' alt='' width='1' height='20' border='0' style='display:block' class='CToWUd'></td>
                                 </tr>
                                 <tr>
                                    <td width='20' style='font-family:verdana,arial!important'><img src='https://ci6.googleusercontent.com/proxy/K9UD7F7-BWO0aVw6xoSNz6TiDMctwkLffAggfmqRkObNkiRZxnZ25ryzdyAngQ5EPh_jT54VaJoLR7ydgNY7_M9V_iAKTrK1P5EjyGI9C6YZQMS2gxaJsEZYVVvTNml0mLcxkx_2WBT5GsQzWwq4YTkgjHc=s0-d-e1-ft#https://developer.magento.com/skin/frontend/rwd/developer_portal/images/emails/blank_email.gif' alt='' width='20' height='1' border='0' style='display:block' class='CToWUd'></td>
                                    <td style='font-family:verdana,arial!important'>
                                       <table class='m_-4013470537037416767pad_null' cellspacing='0' cellpadding='0' width='608' style='font-family:verdana,arial!important;border-collapse:collapse'>
                                          <tbody>
                                             <tr>
                                                <td style='font-family:verdana,arial!important'><a href=''><img width='100' style='background-color:white' src='http://magento.maddaai.it/pub/media/logo/stores/1/maddaai-logo.png' alt='Magento Marketplace' border='0' class='CToWUd'></a></td>
                                                <td valign='middle' align='right' style='font-family:verdana,arial!important'>
                                                   <table class='m_-4013470537037416767pad_null' cellspacing='0' cellpadding='0' style='font-family:verdana,arial!important;border-collapse:collapse'>
                                                      <tbody>
                                                         <tr valign='middle'>
                                                            <td style='font-family:verdana,arial!important'><a href='http://mail.magento.com/wf/click?upn=eMU1GYGCFQiqi1zVmwTtDdWcs2j-2Fn3Rg7hvgcHr4XWp3IZ3Hrp8uIobghVZ-2FQtJW_gJ5ORFI-2BLCaunNoeQsLPaWHt1cio8iG51tRLS-2FGjyH9O1JAd8nWFz5U6KrtwoktoI2FOqq7FPb1c02ruJScJI7b9cTDuY0yqgP7t7nZJFM-2B7VM1OX2qballjiNcw21fr1Y5UakOei6IXkNn1wWYtG-2FdcrJWuRg3YW-2FeoW7VDqrvxJFEKiOc-2FlXFR6P2O4UIUxzKmJiVviIwF7r-2FTKTOshHo97x8PNUzCPWzBg8mcodM-3D' target='_blank' data-saferedirecturl='https://www.google.com/url?hl=it&amp;q=http://mail.magento.com/wf/click?upn%3DeMU1GYGCFQiqi1zVmwTtDdWcs2j-2Fn3Rg7hvgcHr4XWp3IZ3Hrp8uIobghVZ-2FQtJW_gJ5ORFI-2BLCaunNoeQsLPaWHt1cio8iG51tRLS-2FGjyH9O1JAd8nWFz5U6KrtwoktoI2FOqq7FPb1c02ruJScJI7b9cTDuY0yqgP7t7nZJFM-2B7VM1OX2qballjiNcw21fr1Y5UakOei6IXkNn1wWYtG-2FdcrJWuRg3YW-2FeoW7VDqrvxJFEKiOc-2FlXFR6P2O4UIUxzKmJiVviIwF7r-2FTKTOshHo97x8PNUzCPWzBg8mcodM-3D&amp;source=gmail&amp;ust=1492954961055000&amp;usg=AFQjCNGqWmumrNRjdmGjzGNlU3SxjXngrA'><img src='https://ci5.googleusercontent.com/proxy/KwbP0OzSFXzUUU3qb3K5a3IZhJR_Kat7gFYQaOMlwe6mkUY_wKDFc49kNscTzzcO9JgL-QVEzIalf5uOuP8-XGYgOnjdSkC60avOO11G48zF5xuvgbcSw-nwa4-imhDOoGiUcExVDl5cS5UGfmi14gJ8OVqE2uUVtizvjQoKSTcsdAo=s0-d-e1-ft#https://developer.magento.com/skin/frontend/rwd/developer_portal/images/emails/facebook_icon_footer_email.png' alt='Facebook' width='30' height='20' border='0' style='display:block' class='CToWUd'></a></td>
                                                            <td style='font-family:verdana,arial!important'><a href='http://mail.magento.com/wf/click?upn=JYMGGWE1ymfT0UuhOCEU4HjYtOEHjEdlzM7byjlW2qhiL2s8NfCIRS0dnwyCx4Jo_gJ5ORFI-2BLCaunNoeQsLPaWHt1cio8iG51tRLS-2FGjyH9O1JAd8nWFz5U6KrtwoktoI2FOqq7FPb1c02ruJScJI2zYKwVU95q-2Fhpsr1O3B2Y0oAV4fwQ5c9ViueLuqgOQswA8UVFSJ4QvfYPE6NYoTO-2BQU8wvuvwOGGY1gjoUSCUb2fBl6Z-2FlY5kD6HXm9d3uO-2BJm8zDBd3CKAPDKa9zp5wYa3dOmmGvN0RoJuOVUVGR0-3D' target='_blank' data-saferedirecturl='https://www.google.com/url?hl=it&amp;q=http://mail.magento.com/wf/click?upn%3DJYMGGWE1ymfT0UuhOCEU4HjYtOEHjEdlzM7byjlW2qhiL2s8NfCIRS0dnwyCx4Jo_gJ5ORFI-2BLCaunNoeQsLPaWHt1cio8iG51tRLS-2FGjyH9O1JAd8nWFz5U6KrtwoktoI2FOqq7FPb1c02ruJScJI2zYKwVU95q-2Fhpsr1O3B2Y0oAV4fwQ5c9ViueLuqgOQswA8UVFSJ4QvfYPE6NYoTO-2BQU8wvuvwOGGY1gjoUSCUb2fBl6Z-2FlY5kD6HXm9d3uO-2BJm8zDBd3CKAPDKa9zp5wYa3dOmmGvN0RoJuOVUVGR0-3D&amp;source=gmail&amp;ust=1492954961055000&amp;usg=AFQjCNEzWNHHkNVQQu2rijbNdEmj7GH7Cw'><img src='https://ci5.googleusercontent.com/proxy/Kc3LUIN7Ce2j4eHcavH-3-pksX_Rv-3cEkIAdc-RjFacQfJzphYo09402c5Kc9GWVvSUGr4R0URjOQCdF3J6h7JE4KyH0ElrnIMmi75hESXpxCNl9OHOvMqY_aBUhOQWzL21h9Yw2H9VJhr2sSUPnkCu3rdpYUb-Xr5SlI5oS7bKhA=s0-d-e1-ft#https://developer.magento.com/skin/frontend/rwd/developer_portal/images/emails/twitter_icon_footer_email.png' alt='Twitter' width='30' height='20' border='0' style='display:block' class='CToWUd'></a></td>
                                                            <td style='font-family:verdana,arial!important'><a href='http://mail.magento.com/wf/click?upn=eMU1GYGCFQiqi1zVmwTtDarAHdOv-2Blyv9Sg-2Bz6TgQimHWNqW2gUvXJW9TW0p5gkssNjIpdCIZOtHLj8QNh9Wrg-3D-3D_gJ5ORFI-2BLCaunNoeQsLPaWHt1cio8iG51tRLS-2FGjyH9O1JAd8nWFz5U6KrtwoktoI2FOqq7FPb1c02ruJScJI2K-2BUNAoDCIxZzeEhZi2fbJuYeVn9KBhhP4aoZyDVgcXwsl2HlvYt4xPDq49W8NWZZ-2FMcm3XUzEe5xT4ZXhy8eZw3aTZj9TWskB02JZCfNnU5UxXhKUqFNVneyxaVXENvOLsqNhpWSVAUaMCKz5nq90-3D' target='_blank' data-saferedirecturl='https://www.google.com/url?hl=it&amp;q=http://mail.magento.com/wf/click?upn%3DeMU1GYGCFQiqi1zVmwTtDarAHdOv-2Blyv9Sg-2Bz6TgQimHWNqW2gUvXJW9TW0p5gkssNjIpdCIZOtHLj8QNh9Wrg-3D-3D_gJ5ORFI-2BLCaunNoeQsLPaWHt1cio8iG51tRLS-2FGjyH9O1JAd8nWFz5U6KrtwoktoI2FOqq7FPb1c02ruJScJI2K-2BUNAoDCIxZzeEhZi2fbJuYeVn9KBhhP4aoZyDVgcXwsl2HlvYt4xPDq49W8NWZZ-2FMcm3XUzEe5xT4ZXhy8eZw3aTZj9TWskB02JZCfNnU5UxXhKUqFNVneyxaVXENvOLsqNhpWSVAUaMCKz5nq90-3D&amp;source=gmail&amp;ust=1492954961055000&amp;usg=AFQjCNH7uRSdRsFqwZ546FHYiGUJ6JESYA'><img src='https://ci4.googleusercontent.com/proxy/phSCa3bE3PjG1u_dwk4S3x_l8hvNfhYnaEKoxNPA0iSecX9mEhz1bHztz6ltjigBUpQ5UsrVRimvW_RNsu0WN3OYS8OHCpJF7bXprTLYTPpVT3NMIQj7p17h7gNpjqcgEG7ryIL_OOBwrMg9PvIkceiYg3dWZ3nzuGOpFqI2S1OjGQ=s0-d-e1-ft#https://developer.magento.com/skin/frontend/rwd/developer_portal/images/emails/youtube_icon_footer_email.png' alt='Youtube' width='30' height='20' border='0' style='display:block' class='CToWUd'></a></td>
                                                            <td style='font-family:verdana,arial!important'><a href='http://mail.magento.com/wf/click?upn=eMU1GYGCFQiqi1zVmwTtDbgd0ue7ExgfQWPwP7eGLXmiLdpQZ5c1TlehcaZtxHZWrlPsvD29xDscF7c2NAmWgzBnbAmRLBSYET-2FX6541rAA-3D_gJ5ORFI-2BLCaunNoeQsLPaWHt1cio8iG51tRLS-2FGjyH9O1JAd8nWFz5U6KrtwoktoI2FOqq7FPb1c02ruJScJI1-2FcjVylHX2Oz33peS9E3Muov9MOBI8XpHi7tqYNg5GVFoBnbk2GZClxUwO-2BAws0qBvtT50Whvg6qwfZ6Rtme-2B4veh5ZcooxQT-2FQGVI-2BZSN0g9Lh0KBtiVuLdjJHad2c-2FwBxJkp1xyP0UXyZFpSpI4A-3D' target='_blank' data-saferedirecturl='https://www.google.com/url?hl=it&amp;q=http://mail.magento.com/wf/click?upn%3DeMU1GYGCFQiqi1zVmwTtDbgd0ue7ExgfQWPwP7eGLXmiLdpQZ5c1TlehcaZtxHZWrlPsvD29xDscF7c2NAmWgzBnbAmRLBSYET-2FX6541rAA-3D_gJ5ORFI-2BLCaunNoeQsLPaWHt1cio8iG51tRLS-2FGjyH9O1JAd8nWFz5U6KrtwoktoI2FOqq7FPb1c02ruJScJI1-2FcjVylHX2Oz33peS9E3Muov9MOBI8XpHi7tqYNg5GVFoBnbk2GZClxUwO-2BAws0qBvtT50Whvg6qwfZ6Rtme-2B4veh5ZcooxQT-2FQGVI-2BZSN0g9Lh0KBtiVuLdjJHad2c-2FwBxJkp1xyP0UXyZFpSpI4A-3D&amp;source=gmail&amp;ust=1492954961055000&amp;usg=AFQjCNEjFXl0Cn7hd-tq7RsGz2vUY__ZPg'><img src='https://ci5.googleusercontent.com/proxy/A6oHAfLB54jFjQbnI_TVmf8EJQGlSQtqt-0ga-K1YuWjI568jeiPNgg4-5clXACaxHcZe6qo2ZGnryxVUcqrtJCQG8AV7M2QXFN9iH5qsrZ69QCZAPTuTJN5QWuMloxi2KWsoHB8EdCaH1CUm8EdVX3VzpvmQBZN1PHaqXUhxTCPQvY=s0-d-e1-ft#https://developer.magento.com/skin/frontend/rwd/developer_portal/images/emails/linkedin_icon_footer_email.png' alt='Linkedin' width='25' height='20' border='0' style='display:block' class='CToWUd'></a></td>
                                                         </tr>
                                                      </tbody>
                                                   </table>
                                                </td>
                                             </tr>
                                             <tr>
                                                <td colspan='2' style='font-family:verdana,arial!important'><img src='https://ci6.googleusercontent.com/proxy/K9UD7F7-BWO0aVw6xoSNz6TiDMctwkLffAggfmqRkObNkiRZxnZ25ryzdyAngQ5EPh_jT54VaJoLR7ydgNY7_M9V_iAKTrK1P5EjyGI9C6YZQMS2gxaJsEZYVVvTNml0mLcxkx_2WBT5GsQzWwq4YTkgjHc=s0-d-e1-ft#https://developer.magento.com/skin/frontend/rwd/developer_portal/images/emails/blank_email.gif' alt='' width='1' height='20' border='0' style='display:block' class='CToWUd'></td>
                                             </tr>
                                             <tr>
                                                <td valign='middle' colspan='2' style='font-family:verdana,arial!important'>
                                                   <div style='font-size:12px;line-height:40px;margin:0px;color:rgb(202,195,180);border-top-width:1px;border-top-style:solid;border-top-color:rgb(99,99,99);padding:0px;font-family:verdana,arial!important'>Copyright &copy; 2017</div>
                                                </td>
                                             </tr>
                                          </tbody>
                                       </table>
                                    </td>
                                    <td width='20' style='font-family:verdana,arial!important'><img src='https://ci6.googleusercontent.com/proxy/K9UD7F7-BWO0aVw6xoSNz6TiDMctwkLffAggfmqRkObNkiRZxnZ25ryzdyAngQ5EPh_jT54VaJoLR7ydgNY7_M9V_iAKTrK1P5EjyGI9C6YZQMS2gxaJsEZYVVvTNml0mLcxkx_2WBT5GsQzWwq4YTkgjHc=s0-d-e1-ft#https://developer.magento.com/skin/frontend/rwd/developer_portal/images/emails/blank_email.gif' alt='' width='20' height='1' border='0' style='display:block' class='CToWUd'></td>
                                 </tr>
                                 <tr>
                                    <td colspan='3' style='font-family:verdana,arial!important'><img src='https://ci6.googleusercontent.com/proxy/K9UD7F7-BWO0aVw6xoSNz6TiDMctwkLffAggfmqRkObNkiRZxnZ25ryzdyAngQ5EPh_jT54VaJoLR7ydgNY7_M9V_iAKTrK1P5EjyGI9C6YZQMS2gxaJsEZYVVvTNml0mLcxkx_2WBT5GsQzWwq4YTkgjHc=s0-d-e1-ft#https://developer.magento.com/skin/frontend/rwd/developer_portal/images/emails/blank_email.gif' alt='' width='1' height='10' border='0' style='display:block' class='CToWUd'></td>
                                 </tr>
                              </tbody>
                           </table>
                        </td>
                     </tr>
                  </tbody>
               </table>
            </td>
         </tr>
         <tr>
            <td style='font-family:verdana,arial!important'><img src='https://ci6.googleusercontent.com/proxy/K9UD7F7-BWO0aVw6xoSNz6TiDMctwkLffAggfmqRkObNkiRZxnZ25ryzdyAngQ5EPh_jT54VaJoLR7ydgNY7_M9V_iAKTrK1P5EjyGI9C6YZQMS2gxaJsEZYVVvTNml0mLcxkx_2WBT5GsQzWwq4YTkgjHc=s0-d-e1-ft#https://developer.magento.com/skin/frontend/rwd/developer_portal/images/emails/blank_email.gif' alt='' width='1' height='10' border='0' style='display:block' class='CToWUd'></td>
         </tr>
      </tbody>
   </table>
</div>";
    }	
			
	}


