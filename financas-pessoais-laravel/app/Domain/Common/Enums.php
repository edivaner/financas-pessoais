<?php
namespace App\Domain\Common;

enum TipoCartao:string {
    case MULTIPLO='MULTIPLO';
    case CREDITO='CREDITO';
    case DEBITO='DEBITO';
}

enum TipoCategoria:string {
    case DESPESAS='DESPESAS';
    case CREDITO='CREDITO';
    case INVESTIMENTOS='INVESTIMENTOS';
}

enum TipoLancamento:string {
    case RECEITAS='RECEITAS';
    case DESPESAS='DESPESAS';
    case TRANSFERENCIA='TRANSFERENCIA';
    case INVESTIMENTOS='INVESTIMENTOS';
}

enum TipoImagem:string {
    case LOGO='LOGO';
    case LANCAMENTO='LANCAMENTO';
    case AVATAR='AVATAR';
}

enum Cargo:string {
    case ADM='ADM';
    case SUPORTE='SUPORTE';
    case CLIENTE='CLIENTE';
}

enum TipoInvestimento:string {
    case INVESTIR='INVESTIR';
    case RESGATAR='RESGATAR';
}

enum PeriodicidadeLimite:string {
    case MENSAL='MENSAL';
}

enum PurposeOtp:string {
    case SIGNUP='signup';
    case LOGIN='login';
    case RESET='reset';
}
