
1. Status da Assembleia (assembleia_status)
enum AssembleiaStatus: string
{
    case DRAFT = 'rascunho';           // criação inicial
    case SCHEDULED = 'agendada';       // data definida
    case CALLED = 'convocada';         // edital enviado
    case IN_PROGRESS = 'em_andamento'; // ocorrendo agora
    case SUSPENDED = 'suspensa';       // pausada
    case FINISHED = 'encerrada';       // terminou
    case CANCELED = 'cancelada';       // cancelada
    case POSTPONED = 'adiada';         // remarcada
    case NO_QUORUM = 'sem_quorum';     // não atingiu quorum
}

Regras importantes:
convocada → em_andamento
em_andamento → encerrada
encerrada → (gera ata)
sem_quorum normalmente encerra o fluxo


2. Status da Ata (ata_status)

enum AtaStatus: string
{
    case NOT_STARTED = 'nao_iniciada';   // assembleia ainda não gerou ata
    case DRAFT = 'rascunho';             // sendo redigida
    case REVIEW = 'em_revisao';          // revisão interna
    case PENDING_APPROVAL = 'em_aprovacao'; // aguardando aprovação (síndico/conselho)
    case APPROVED = 'aprovada';          // validada
    case REJECTED = 'rejeitada';         // precisa ajustes
    case SIGNED = 'assinada';            // assinada (opcional)
    case EM_LAVRATURA = 'em_lavratura';  // em lavratura
    case REGISTERED = 'registrada';      // registrada em cartório (opcional)
    case PUBLISHED = 'publicada';        // disponibilizada aos condôminos
}

3. Status das Deliberações (deliberacao_status)

enum DeliberacaoStatus: string
{
    case PENDING = 'pendente';        // ainda não votada
    case IN_VOTING = 'em_votacao';    // votação em andamento
    case APPROVED = 'aprovada';       // aprovada
    case REJECTED = 'rejeitada';      // rejeitada
    case TIED = 'empatada';           // empate
    case CANCELED = 'cancelada';      // retirada de pauta
    case SUSPENDED = 'suspensa';      // votação pausada
}

Relação entre os três (muito importante)
Fluxo típico:
Assembleia → em_andamento
Deliberações → em_votacao → aprovada/rejeitada
Assembleia → encerrada
Ata → rascunho → revisao → aprovada → publicada