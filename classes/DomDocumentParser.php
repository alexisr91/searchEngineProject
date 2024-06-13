<?php

    class DomDocumentParser{
        
        public function __construct($url){

           /*  echo "URL : $url"; */

           $options = array(

            // transmission de flux de données dans un tableau 
                'http'=> array(
                    'method'=>'GET',
                    'header'=>'User-Agent: MongMongBot/1.0\n')
           );

           $context = stream_context_create($options);

           // chargement du document HTML

           $this->_doc = new DomDocument();
           @$this->_doc->loadHTML(file_get_contents($url,false,$context));


        }

        public function getLinks(){
            // Ceci n'est pas un getter puisqu'on ne renvoie pas la valeur d'un attribut de l'objet et il en va de même pour les setters
            return $this->_doc->getElementsByTagName("a");
        }

        public function getTitleTags(){

            return $this->_doc->getElementsByTagName("title");
        }

        public function getMetatags(){
            return $this->_doc->getElementsByTagName("meta");
        }

        // récuperation des images ( src, alt, title)

        public function getImageTags(){

            return $this->_doc->getElementsByTagName("img");
        }
    }

?>