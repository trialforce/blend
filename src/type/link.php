<?php

namespace Type;

/**
 * Classe para lidar com strings
 *
 * Criado originalmente em 01/07/2011 por :
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Na Solis - Cooperativa de Soluções Livres Ltda. e Univates - Centro Universitário.
 *
 * */
class Link extends \Type\Text
{

    /**
     * Contrutor estático usado para que possa se utilizar
     * o construtor e chamar a função necessária na mesma linha.
     *
     * @param string $string
     * @param string $encoding
     *
     * @return \Type\Link
     *
     * @example \Type\Link::construct( $string )->generate() = retorna a string em formato de usuário
     */
    public static function construct($string = null, $encoding = 'UTF-8')
    {
        return new \Type\Link($string, $encoding);
    }

    /**
     * Função para Converter Strings em formato válido em URLs
     * 'Guaporé é uma coisa' -> 'guapore-e-uma-coisa'
     *
     * @param string $separador
     * @param type $limit
     * @return \Type\Text
     */
    public function toUrl($separador = '-', $limit = 100)
    {
        //Retirando os espaços essesivos e as tags HTML e PHP da string
        $this->removeHtml();
        //Quebrando a string para um tamanho pré-definido
        $this->sub(0, $limit);
        //Substituindo caracteres especiais pela letra respectiva
        $this->toASCII();
        //Substituindo caracteres nao permitidos outros caracteres por "$sep"
        $this->string = preg_replace('/[^a-zA-Z0-9]/', $separador, $this->string);
        //Removendo o excesso de "$sep" por apenas um
        $this->string = preg_replace('/(' . $separador . ')+/', $separador, $this->string);
        //Removendo o "$sep" do inicio e fim da string
        $this->string = preg_replace('/(^(' . $separador . ')+)|((' . $separador . ')+$)/i', '', $this->string);

        return $this;
    }

    public function removeHtml()
    {
        $this->string = strip_tags(html_entity_decode($this->string));
        $this->trim();

        return $this;
    }

    // Torna clicáveis links em puro texto
    public function toLinks()
    {
        $find = array(
            '/(^|\s|>)(www.[^<> \n\r]+)/iex',
            '/(^|\s|>)([_A-Za-z0-9-]+(\\.[A-Za-z]{2,3})?\\.[A-Za-z]{2,4}\\/[^<> \n\r]+)/iex',
            '/(?(?=<a[^>]*>.+<\/a>)(?:<a[^>]*>.+<\/a>)|([^="\']?)((?:https?):\/\/([^<> \n\r]+)))/iex',
            '/(^|\s|>)([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,}[^<> \n\r]+)/iex'
        );
        $replace = array(
            "stripslashes((strlen('\\2')>0?'\\1<a href=\"http://\\2\" target=\"_blank\" rel=\"nofollow\">\\2</a> \\3':'\\0'))",
            "stripslashes((strlen('\\2')>0?'\\1<a href=\"http://\\2\" target=\"_blank\" rel=\"nofollow\">\\2</a> \\4':'\\0'))",
            "stripslashes((strlen('\\2')>0?'\\1<a href=\"\\2\" target=\"_blank\" rel=\"nofollow\">\\3</a> ':'\\0'))",
            "stripslashes((strlen('\\2')>0?'\\1<a href=\"mailto:\\2\">\\2</a> \\4':'\\0'))"
        );

        return preg_replace($find, $replace, $this->string);
    }

    public function toHuman()
    {
        return $this->__toString();
    }

}
