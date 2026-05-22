# 概念マップ (Concept Graph)

主要語彙の関係を俯瞰する図  各 vocab ファイルの `**関連**:` 行を統合した「学習・議論用の地図」

詳細は対応する `vocab-*.md` を参照

## クラス責務クラスタ (構造の臭い ↔ 設計原則 ↔ リファクタリング手法)

```mermaid
graph LR
  SRP[SRP<br/>単一責任の原則]
  LongMethod[Long Method]
  GodClass[God Class]
  DivergentChange[Divergent Change]
  ShotgunSurgery[Shotgun Surgery]

  ExtractMethod[Extract Method]
  ExtractClass[Extract Class]
  MoveMethod[Move Method]

  Cohesion[凝集度・結合度]

  LongMethod --> ExtractMethod
  GodClass --> ExtractClass
  GodClass --> SRP
  DivergentChange --> SRP
  DivergentChange --> ExtractClass
  ShotgunSurgery --> MoveMethod
  SRP --> Cohesion
```

## 拡張性クラスタ (分岐 ↔ ポリモーフィズム)

```mermaid
graph LR
  Switch[Switch Statements]
  OCP[OCP<br/>開放閉鎖の原則]
  RCwP[Replace Conditional<br/>with Polymorphism]
  LSP[LSP<br/>リスコフの置換原則]
  Strategy[Strategy パターン]

  Switch --> RCwP
  Switch --> OCP
  RCwP --> Strategy
  OCP --> LSP
  OCP --> Strategy
```

## 依存・テスト容易性クラスタ

```mermaid
graph LR
  HardCoded[Hard-coded<br/>Dependency]
  DI[Dependency Injection]
  DIP[DIP<br/>依存性逆転の原則]
  Seam[Seam]
  Testability[Testability]
  ISP[ISP<br/>Interface 分離]

  PureFunction[Pure Function]
  SideEffectBoundary[副作用境界]

  TestDouble[Test Double<br/>Dummy / Fake / Stub<br/>/ Mock / Spy]

  HardCoded --> Seam
  HardCoded --> DI
  DI --> DIP
  Seam --> Testability
  DI --> Testability
  Testability --> TestDouble
  SideEffectBoundary --> PureFunction
  PureFunction --> Testability
  DIP --> ISP
```

## データ・型クラスタ

```mermaid
graph LR
  PrimitiveObsession[Primitive Obsession]
  DataClump[Data Clump]
  LongParam[Long Parameter List]

  ReplacePrim[Replace Primitive<br/>with Object]
  IntroduceParam[Introduce<br/>Parameter Object]

  ValueObject[Value Object]
  Entity[Entity]

  PrimitiveObsession --> ReplacePrim
  DataClump --> IntroduceParam
  DataClump --> ValueObject
  LongParam --> IntroduceParam
  LongParam --> DataClump
  ReplacePrim --> ValueObject
  ValueObject -.対.- Entity
```

## 振る舞いの置き場所クラスタ (DDD の核)

```mermaid
graph LR
  FeatureEnvy[Feature Envy]
  AnemicModel[Anemic<br/>Domain Model]
  TellDontAsk[Tell Don't Ask]
  Demeter[Law of Demeter]

  MoveMethod[Move Method]
  AggregateRoot[Aggregate Root]
  DomainService[Domain Service]
  ApplicationService[Application Service]

  FeatureEnvy --> MoveMethod
  FeatureEnvy --> TellDontAsk
  AnemicModel --> TellDontAsk
  AnemicModel --> DomainService
  AnemicModel --> ApplicationService
  Demeter --> TellDontAsk
  AggregateRoot --> TellDontAsk
  ApplicationService -.対.- DomainService
```

## アーキ層・境界クラスタ

```mermaid
graph LR
  LayerResp[レイヤー責務]
  DependencyDir[依存方向]
  Boundary[Boundary<br/>境界]
  DTO[DTO]

  CleanArch[Clean Architecture]
  Hexagonal[Hexagonal<br/>Ports & Adapters]
  Layered[Layered<br/>Architecture]

  ACL[Anti-Corruption Layer]
  BoundedContext[Bounded Context]
  UseCase[Use Case]
  Presenter[Presenter / ViewModel]

  Layered --> LayerResp
  CleanArch --> DependencyDir
  Hexagonal --> Boundary
  Hexagonal --> ACL
  Boundary --> DTO
  Boundary --> ACL
  ACL --> BoundedContext
  UseCase --> LayerResp
  Presenter --> LayerResp
```

## 判断軸クラスタ (議論のメタレベル)

```mermaid
graph LR
  YAGNI[YAGNI]
  KISS[KISS]
  SpecGen[Speculative<br/>Generality]
  PrematureOpt[Premature<br/>Optimization]

  TwoWayDoor[Two-way door<br/>/ One-way door]
  LRM[Last Responsible<br/>Moment]
  CostOfChange[Cost of Change<br/>Curve]

  Pareto[Pareto / 80-20]
  HotPath[Hot Path<br/>/ Cold Path]
  BoyScout[Boy Scout Rule]
  TechDebt[Tech Debt]

  Bikeshedding[Bikeshedding]
  Hyrum[Hyrum's Law]
  Cargo[Cargo Cult]
  Confirmation[Confirmation Bias]

  YAGNI -.- KISS
  YAGNI --> SpecGen
  TwoWayDoor --> LRM
  TwoWayDoor --> CostOfChange
  PrematureOpt --> Pareto
  Pareto --> HotPath
  BoyScout --> TechDebt
```

## 認知負荷クラスタ (コード表現、[vocab-cognitive-load.md](vocab-cognitive-load.md))

```mermaid
graph LR
  WorkingMemory[Working Memory]
  Chunking[Chunking]
  Schema[Schema]
  CognitiveLoad[Cognitive Load Theory]

  Naming[命名の予測可能性]
  Levels[Levels of<br/>Indirection]
  Nest[ネストの深さ]
  StateLocality[状態の散在 vs 局在]
  ContextSwitch[Context Switch]
  Consistency[Consistency]

  CognitiveLoad --> WorkingMemory
  WorkingMemory --> Chunking
  Chunking --> Schema
  Naming --> Schema
  Levels --> WorkingMemory
  Nest --> WorkingMemory
  StateLocality --> WorkingMemory
  ContextSwitch --> StateLocality
  Consistency --> Schema
```

## コミュニケーションクラスタ (UI / 文書 / 会話、[vocab-communication.md](vocab-communication.md))

```mermaid
graph LR
  Hicks[Hick's Law]
  Fitts[Fitts's Law]
  Gestalt[Gestalt<br/>Principles]
  Whitespace[Whitespace]
  Color[Color Palette<br/>Limit]

  TLDR[TL;DR<br/>Summary First]
  Heading[Heading<br/>Hierarchy]
  OneIdea[One Idea<br/>per Paragraph]
  TermConsistency[Term Consistency]
  VisualAid[Visual Aid]

  PREP[PREP]
  BLUF[Bottom Line<br/>Up Front]
  Pyramid[Pyramid<br/>Principle]
  RuleOfThree[Rule of Three]

  SelectiveEmphasis[Selective<br/>Emphasis]

  Hicks --> SelectiveEmphasis
  Gestalt --> Whitespace
  TLDR -.- BLUF
  BLUF --> Pyramid
  Pyramid --> RuleOfThree
  PREP --> RuleOfThree
  Color --> SelectiveEmphasis
  Fitts --> SelectiveEmphasis
```

## クラスタ間の太いつながり

```mermaid
graph LR
  ClassResp[クラス責務<br/>SRP / Long Method / God Class]
  ExtTest[依存・テスト<br/>DI / DIP / Seam]
  Behavior[振る舞いの置き場<br/>Feature Envy / Tell Don't Ask]
  DataType[データ・型<br/>VO / Primitive Obsession]
  Arch[アーキ層・境界<br/>Layer / Boundary / DTO]
  Judge[判断軸<br/>YAGNI / KISS / Pareto]
  Cognitive[認知負荷<br/>命名 / ネスト / 状態]
  Comm[コミュニケーション<br/>UI / 文書 / プレゼン]

  ClassResp <--> ExtTest
  ClassResp <--> Behavior
  Behavior <--> DataType
  ClassResp <--> Arch
  ExtTest <--> Arch
  Behavior <--> Arch
  Cognitive --> ClassResp
  Cognitive --> Behavior
  Cognitive -.共通原則.- Comm
  Judge -.メタ.-> ClassResp
  Judge -.メタ.-> ExtTest
  Judge -.メタ.-> Arch
```

## 使い方

**学習用**: クラスタ単位で学ぶと記憶が定着する  個別語彙より「群れの中での位置付け」で覚える

**議論用**: 議論が空中戦になったら、現在の論点がどのクラスタかを最初に揃える  例: 「Long Method の話 (クラス責務クラスタ) と Hard-coded Dependency の話 (依存・テスト容易性クラスタ) は別軸なので分けて議論する」

**Skill 用**: 検出した指摘がどのクラスタに属するかで関連指摘の探索範囲を絞れる  例: SRP 違反を検出したら同クラスタの Long Method / God Class / Divergent Change も同時にチェック

## 注意

- 本図は **網羅ではなく俯瞰**  個別の関係性は各 `vocab-*.md` の `**関連**:` 行を参照
- 太いつながり図は概念整理のための簡略化  実際にはクラスタ間に多数のクロス参照がある
- 図の更新は [INDEX.md](INDEX.md) との整合性を保つこと
