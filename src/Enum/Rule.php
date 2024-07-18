<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Enum;

enum Rule: string
{
    use EnumMethod;

    /**
     * 待验证字段必须是 yes ，on ，1 或 true。这对于验证「服务条款」的接受或类似字段时很有用。
     * @link https://learnku.com/docs/laravel/9.x/validation/12219#rule-date-equals
     */
    case ACCEPTED = 'accepted';

    /**
     * accepted_if:anotherField,value,…
     * 如果另一个正在验证的字段等于指定的值，则验证中的字段必须为 yes ，on ，1 或 true。
     * 这对于验证「服务条款」接受或类似字段很有用。
     */
    case ACCEPTED_IF = 'accepted_if';
    /**
     * 根据 dns_get_record PHP 函数，验证中的字段必须具有有效的 A 或 AAAA 记录。
     * 提供的 URL 的主机名使用 parse_url PHP 函数提取，然后传递给 dns_get_record。
     */
    case ACTIVE_URL = 'active_url';
    /**
     * 验证中的字段必须是给定日期之后的值。日期将被传递给 strtotime PHP 函数中，以便转换为有效的 DateTime 实例：
     * 'start_date' => 'required|date|after:tomorrow'
     * 你也可以指定另一个要与日期比较的字段，而不是传递要由 strtotime 处理的日期字符串：
     * 'finish_date' => 'required|date|after:start_date'
     */
    case AFTER = 'after';
    /**
     * 待验证字段的值对应的日期必须在给定日期之后或与给定的日期相同。
     * 可参阅 after 规则获取更多信息。
     */
    case AFTER_OR_EQUAL = 'after_or_equal';
    /**
     * 待验证字段只能由字母组成。
     */
    case ALPHA = 'alpha';
    /**
     * 待验证字段可以含有字母、数字，短破折号（-）和下划线（_）。
     */
    case ALPHA_DASH = 'alpha_dash';
    /**
     * 待验证字段只能由字母和数字组成。
     */
    case ALPHA_NUM = 'alpha_num';
    /**
     * 待验证字段必须是有效的 PHP 数组。
     * 当向 array 规则提供附加值时，输入数组中的每个键都必须出现在提供给规则的值列表中。
     */
    case IS_ARRAY = 'array';
    /**
     * 待验证字段的值对应的日期必须在给定的日期之前。
     * 日期将会传递给 PHP 函数 strtotime。
     * 此外，与 after 规则一致，可以将另外一个待验证的字段作为 date 的值。
     */
    case BEFORE = 'before';
    /**
     * 待验证字段的值对应的日期必须在给定的日期之前。
     * 日期将会传递给 PHP 函数 strtotime。
     * 此外，与 after规则一致，可以将另外一个待验证的字段作为 date 的值。
     */
    case BEFORE_OR_EQUAL = 'before_or_equal';
    /**
     * 验证字段的大小必须在给定的 min 和 max 之间。字符串、数字、数组和文件的计算方式都使用 size 方法。
     */
    case BETWEEN = 'between';
    /**
     * 验证的字段必须可以转换为 Boolean 类型。 可接受的输入为 true, false, 1, 0, "1", 和 "0"。
     */
    case BOOLEAN = 'boolean';
    /**
     * confirmed 可以验证密码和确认密码是否相同，验证字段必须具有匹配字段 {field}_confirmation 。
     * 例如，验证字段为 password ，输入中必须存在与之匹配的 password_confirmation 字段。
     */
    case CONFIRMED = 'confirmed';
    /**
     * 验证字段必须与已验证用户的密码匹配。 您可以使用规则的第一个参数指定 authentication guard：
     * @link https://learnku.com/docs/laravel/9.x/authentication/12239#eaa0d4
     */
    case CURRENT_PASSWORD = 'current_password';
    /**
     * 根据 PHP strtotime 函数，验证的字段必须是有效的日期。
     */
    case IS_DATE = 'date';
    /**
     * 验证字段必须等于给定日期。日期将传递到 PHP strtotime 函数中，以便转换为有效的 DateTime 实例。
     */
    case DATE_EQUALS = 'date_equals';
    /**
     * 验证字段必须匹配给定的 format （日期格式）。
     * 当验证某个字段的时候，你应该只使用 date 或者 date_format 其中一个 ，而不是同时使用。
     * 此验证规则支持 PHP 所有的 DateTime 类。
     */

    case DATE_FORMAT = 'date_format';
    /**
     * 正在验证的字段必须是 "no"，"off"，0 或者 false。
     */
    case DECLINED = 'declined';
    /**
     * declined_if:anotherField,value,…
     * 正在验证的字段必须是 "no"，"off"，0 或者 false。 如果正在验证的另一个字段等于指定值。
     */
    case DECLINED_IF = 'declined_if';
    /**
     * 验证的字段值必须与字段 field 的值不同。
     */
    case DIFFERENT = 'different';
    /**
     * 验证的字段必须为 numeric ，并且必须具有确切长度 value 。
     */
    case DIGITS = 'digits';
    /**
     * digits_between:min,max
     * 验证的字段必须为 numeric ，并且长度必须在给定的 min 和 max 之间。
     */
    case DIGITS_BETWEEN = 'digits_between';
    /**
     * 验证的文件必须是图片并且图片比例必须符合规则:
     * @example 'avatar' => 'dimensions:min_width=100,min_height=200'
     * 可用的规则为: min_width ，max_width ，min_height ，max_height ，width ，height ，ratio 。
     * ratio 约束应该表示为宽度除以高度。 这可以通过像 3/2 这样的语句或像 1.5 这样的 float 来指定：
     * @example 'avatar' => 'dimensions:ratio=3/2'
     * 由于此规则需要多个参数，因此你可以 Rule::dimensions 方法来构造可读性高的规则:
     * @example ------ start ------
     * Validator::make($data, [
     *   'avatar' => [
     *     'required',
     *     Rule::dimensions()->maxWidth(1000)->maxHeight(500)->ratio(3 / 2),
     *   ],
     * ]);
     * @example ------ end ------
     */
    case DIMENSIONS = 'dimensions';
    /**
     * 验证数组时，指定的字段不能有任何重复值：
     * @example 'foo.*.id' => 'distinct'
     * Distinct 默认使用松散的变量比较。要使用严格比较，您可以将 strict 参数添加到验证规则定义中：
     * @example 'foo.*.id' => 'distinct:strict'
     * 你可以在验证规则的参数中添加 ignore_case ，以使规则忽略大小写差异：
     * @example 'foo.*.id' => 'distinct:ignore_case'
     */
    case DISTINCT = 'distinct';
    /**
     * 验证的字段必须符合 e-mail 地址格式。当前版本，此种验证规则由 egulias/email-validator 提供支持。默认情况下，使用 RFCValidation 验证样式，但你也可以应用其他验证样式：
     * @example 'email' => 'email:rfc,dns'
     * 上面的示例将应用 RFCValidation 和 DNSCheckValidation 验证。以下是你可以应用的验证样式的完整列表：
     * @link https://learnku.com/docs/laravel/9.x/validation/12219#rule-date-equals
     */
    case EMAIL = 'email';
    /**
     * ends_with:foo,bar,…
     * 被验证的字段必须以给定值之一结尾。
     */
    case ENDS_WITH = 'ends_with';
    /**
     * Enum 规则是一个基于类的规则，验证指定的字段是否包含一个有效的枚举值。
     * Enum 规则接受枚举的名称作为其唯一的构造函数参数：
     * @example ------ start ------
     * use App\Enums\ServerStatus;
     * use Illuminate\Validation\Rules\Enum;
     * $request->validate([
     *   'status' => [new Enum(ServerStatus::class)],
     * ]);
     * @example ------ end ------
     * @tips 注意：枚举仅适用于 PHP 8.1+。
     */
    case ENUM = 'enum';
    /**
     * validate 和 validated 方法中将会排除掉当前验证的字段。
     */
    case EXCLUDE = 'exclude';
    /**
     * exclude_if:anotherField,value
     * 如果 anotherField 等于 value ，validate 和 validated 方法中会排除掉当前的字段。
     */
    case EXCLUDE_IF = 'exclude_if';
    /**
     * exclude_unless:anotherField,value
     * 除非 anotherField 等于 value ，否则 validate 和 validated 方法中会排除掉当前的字段。
     * 如果 value 为 null （exclude_unless:name,null），那么成立的条件就是被比较的字段（对应规则中的 name ）为 null 或者表单中没有该字段（对应规则中的 name ）。
     */
    case EXCLUDE_UNLESS = 'exclude_unless';
    /**
     * exclude_without:anotherField
     * 如果 anotherField 不在表单数据中，validate 和 validated 方法中会排除掉当前的字段。
     */
    case EXCLUDE_WITHOUT = 'exclude_without';
    /**
     * exists:table,column
     * 验证的字段值必须存在于指定的表中。
     * @example 'state' => 'exists:states'
     * 如果未指定 column 选项，则将使用字段名称。
     * 因此，在这种情况下，该规则将验证 states 数据库表是否包含一条记录，该记录的 state 列的值与请求的 state 属性值匹配。
     * 你可以将验证规则使用的数据库列名称指定在数据库表名称之后：
     * @example 'state' => 'exists:states,abbreviation'
     * 有时候，你或许需要去明确指定一个具体的数据库连接，用于 exists 查询。
     * 你可以通过在表名前面添加一个连接名称来实现这个效果。
     * @example 'email' => 'exists:connection.staff,email'
     * 你可以明确指定 Eloquent 模型，这个模型将被用来确定表名，这样可以代替直接指定表名的方式。
     * @example 'user_id' => 'exists:App\Models\User,id'
     * 如果你想要自定义一个执行查询的验证规则，你可以使用 Rule 类去流畅地定义规则。
     * 在这个例子中，我们也将指定验证规则为一个数组，而不再是使用 | 分割他们：
     * @example ------ start ------
     * use Illuminate\Support\Facades\Validator;
     * use Illuminate\Validation\Rule;
     *
     * Validator::make($data, [
     *   'email' => [
     *   'required',
     *   Rule::exists('staff')->where(function ($query) {
     *       return $query->where('account_id', 1);
     *    }),
     *    ],
     *  ]);
     * @example ------ end ------
     */
    case EXISTS = 'exists';
    /**
     * 要验证的字段必须是一个成功的已经上传的文件。
     */
    case FILE = 'file';
    /**
     * 当字段存在时，要验证的字段必须是一个非空的。
     */
    case FILLED = 'filled';
    /**
     * gt:field
     * 要验证的字段必须要大于被给的字段。这两个字段必须是同一个类型。
     * 字符串、数字、数组和文件都使用 size 进行相同的评估。
     */
    case GT = 'gt';
    /**
     * gte:field
     * 要验证的字段必须要大于或等于被给的字段。这两个字段必须是同一个类型。
     * 字符串、数字、数组和文件都使用 size 进行相同的评估。
     */
    case GTE = 'gte';
    /**
     * 验证身份证件号码有效性
     */
    case ID_CARD = 'regex:~^[0-9]{15,18}(X)?$~i';
    /**
     * 正在验证的文件必须是图像（jpg、jpeg、png、bmp、gif、svg 或 webp）。
     */
    case IMAGE = 'image';
    /**
     * in:foo,bar,…
     * 验证字段必须包含在给定的值列表中。
     * 由于此规则通常要求你 implode 数组，因此可以使用 Rule::in 方法来流畅地构造规则：
     * @example ------- start --------
     * use Illuminate\Support\Facades\Validator;
     * use Illuminate\Validation\Rule;
     * Validator::make($data, [
     *     'zones' => [
     *     'required',
     *      Rule::in(['first-zone', 'second-zone']),
     *     ],
     *   ]);
     * @example ------- end --------
     * 当 in 规则与 array 规则结合使用时，输入数组中的每个值都必须出现在提供给 in 规则的值列表中。
     * 在以下示例中，输入数组中的「LAS」机场代码无效，因为它不包含在提供给 in 规则的机场列表中：
     * @example ------- start --------
     * use Illuminate\Support\Facades\Validator;
     * use Illuminate\Validation\Rule;
     * $input = [
     * 'airports' => ['NYC', 'LAS'],
     * ];
     * Validator::make($input, [
     *     'airports' => [
     *     'required',
     *     'array',
     *     Rule::in(['NYC', 'LIT']),
     *   ],
     * ]);
     * @example ------- end --------
     */
    case IN = 'in';
    /**
     * in_array:anotherField.*
     * 正在验证的字段必须存在于 anotherField 的值中。
     */
    case IN_ARRAY = 'in_array';
    /**
     * 验证字段必须是整数。
     * 注意：此验证规则不会验证输入是否为 “整数” 变量类型，仅验证输入是否为 PHP 的 FILTER_VALIDATE_INT 规则所接受的类型。
     * 如果您需要将输入验证为数字，请将此规则与 numeric 验证规则 结合使用。
     */
    case INTEGER = 'integer';
    /**
     * 验证的字段必须是 IP 地址。
     */
    case IP = 'ip';
    /**
     * 验证的字段必须是 IPv4 地址。
     */
    case IPV4 = 'ipv4';
    /**
     * 验证的字段必须是 IPv6 地址。
     */
    case IPV6 = 'ipv6';
    /**
     * lt:field
     * 验证的字段必须小于给定的字段。这两个字段必须是相同的类型。
     * 字符串、数值、数组和文件大小的计算方式与 size 方法进行评估。
     */
    case LT = 'lt';
    /**
     * lte:field
     * 验证中的字段必须小于或等于给定的字段 。这两个字段必须是相同的类型。
     * 字符串、数值、数组和文件大小的计算方式与 size 方法进行评估。
     */
    case LTE = 'lte';
    /**
     * 验证的字段必须是有效的 JSON 字符串。
     */
    case JSON = 'json';
    /**
     * 验证字段必须是 MAC 地址。
     */
    case MAC_ADDRESS = 'mac_address';
    /**
     * max:value
     * 验证中的字段必须小于或等于 value 。
     * 字符串、数字、数组或是文件大小的计算方式都用 size 规则。
     */
    case MAX = 'max';
    /**
     * mimetypes:text/plain,…
     * 验证的文件必须具备与列出的其中一个扩展相匹配的 MIME 类型：
     * @example 'video' => 'mimetypes:video/avi,video/mpeg,video/quicktime'
     * 为了确定上传文件的 MIME，框架将会读取文件，然后自动推测文件 MIME 类型，这可能与客户端提供的 MIME 类型不一致。
     */
    case MIMETYPES = 'mimetypes';
    /**
     * mimes:foo,bar,…
     * 验证的文件必须具有与列出的其中一个扩展名相对应的 MIME 类型。
     * @example 'photo' => 'mimes:jpg,bmp,png'
     * 即使你可能只需要验证指定扩展名，但此规则实际上会去验证文件的 MIME 类型，其通过读取文件内容来推测它的 MIME 类型。
     * 可以在以下链接中找到完整的 MIME 类型列表及相对应的扩展名：
     * @link https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types
     */
    case MIMES = 'mimes';
    /**
     * min:value
     * 验证字段必须具有最小值 value 。字符串，数值，数组，文件大小的计算方式都与 size 规则一致。
     */
    case MIN = 'min';
    /**
     * 验证字段必须是 value 的倍数。
     * @tips 注意：使用 multiple_of 规则时，需要安装 bcmath PHP 扩展
     * @link https://www.php.net/manual/en/book.bc.php
     */
    case MULTIPLE_OF = 'multiple_of';
    /**
     * 匹配国内11位手机手机号码
     */
    case MOBILE = 'regex:~^1\d{10}$~';
    /**
     * 匹配EPP格式的国际电话号码
     */
    case MOBILE_INTERNATIONAL = 'regex:~^\+\d{1,3}-*\.*\d{4,14}(?:x.+)?$~';
    /**
     * not_in:foo,bar,…
     * 验证字段不能包含在给定的值的列表中。 使用 Rule::notIn 方法可以更流畅的构建这个规则：
     * @example ------- start --------
     * use Illuminate\Validation\Rule;
     * Validator::make($data, [
     *   'toppings' => [
     *   'required',
     *   Rule::notIn(['sprinkles', 'cherries']),
     *   ],
     * ]);
     * @example ------- end --------
     */
    case NOT_IN = 'not_in';
    /**
     * 验证字段必须与给定的正则表达式不匹配。
     *
     * 验证时，这个规则使用 PHP preg_match 函数。指定的模式应遵循 preg_match 所需的相同格式，也包括有效的分隔符。 例如：
     * @example 'email' => 'not_regex:/^.+$/i'
     * @tips 注意：当使用 regex / not_regex 模式时， 可能需要在数组中指定规则，而不是使用 | 分隔符 ，特别是在正则表达式包含 | 字符 的情况下。
     */
    case NOT_REGEX = 'not_regex';
    /**
     * 验证字段可以为 null。
     */
    case NULLABLE = 'nullable';
    /**
     * 验证字段必须为 数值。
     */
    case NUMERIC = 'numeric';
    /**
     * 验证字段必须存在于输入数据中，但可以为空。
     */
    case PRESENT = 'present';
    /**
     * 验证字段必须为空或不存在。
     */
    case PROHIBITED = 'prohibited';
    /**
     * prohibited_if:anotherField,value,…
     * 如果 anotherField 字段等于任何值，则验证中的字段必须为空或不存在。
     */
    case PROHIBITED_IF = 'prohibited_if';
    /**
     * prohibited_unless:anotherfield,value,…
     * 验证中的字段必须为空或不存在，除非 anotherField 字段等于 value 。
     */
    case PROHIBITED_UNLESS = 'prohibited_unless';
    /**
     * prohibits:anotherField,…
     * 如果验证中的字段存在，则 anotherField 中不能存在任何字段，即使该字段为空。
     */
    case PROHIBITS = 'prohibits';
    /**
     * regex:pattern
     * 验证字段必须与给定的正则表达式匹配。
     * 验证时，这个规则使用 PHP 的 preg_match 函数。 指定的模式应遵循 preg_match 所需的相同格式，也包括有效的分隔符。
     * @example 'email' => 'regex:/^.+@.+$/i'
     * @tips 注意：当使用 regex / not_regex 模式时， 可能需要在数组中指定规则，而不是使用 | 分隔符 ，特别是在正则表达式包含 | 字符 的情况下。
     */
    case REGEX = 'regex';
    /**
     * 验证中的字段必须存在于输入数据中，并且不能为空。如果以下条件之一为真，则字段被视为「空」：
     * @item 值为 null。
     * @item 该值为空字符串。
     * @item 该值是一个空数组或空的 Countable 对象。
     * @item 该值是一个没有路径的上传文件。
     */
    case REQUIRED = 'required';
    /**
     * required_if:anotherField,value,…
     * 如果 anotherField 字段等于任何 value ，则验证中的字段必须存在且不为空。
     *
     * 如果你想为 required_if 规则构造一个更复杂的条件，你可以使用 Rule::requiredIf 方法。
     * 此方法接受布尔值或闭包。 当传递一个闭包时，闭包应该返回 true 或 false 以指示是否需要验证字段：
     * @example ------- start --------
     * use Illuminate\Support\Facades\Validator;
     * use Illuminate\Validation\Rule;
     * Validator::make($request->all(), [
     *   'role_id' => Rule::requiredIf($request->user()->is_admin),
     * ]);
     *
     * Validator::make($request->all(), [
     *   'role_id' => Rule::requiredIf(function () use ($request) {
     *      return $request->user()->is_admin;
     *    }),
     * ]);
     * @example ------- end --------
     */
    case REQUIRED_IF = 'required_if';
    /**
     * required_unless:anotherField,value,…
     * 除非 anotherField 字段等于任何 value ，否则验证中的字段必须存在且不为空。
     * 这也意味着 anotherField 必须存在于请求数据中，除非 value 为 null。
     * 如果 value 为 null (required_unless:name,null)，则需要验证的字段，除非比较字段为 null 或请求数据中缺少比较字段。
     */
    case REQUIRED_UNLESS = 'required_unless';
    /**
     * required_with:foo,bar,…
     * 仅当任何指定的字段存在且不为空时，验证下的字段才必须存在且不为空。
     */
    case REQUIRED_WITH = 'required_with';
    /**
     * required_with_all:foo,bar,…
     * 仅当所有指定的字段存在且不为空时，验证下的字段才必须存在且不为空。
     */
    case REQUIRED_WITH_ALL = 'required_with_all';
    /**
     * required_without:foo,bar,…
     * 仅当任何指定的字段不存在或为空时，验证下的字段才必须存在且不为空。
     */
    case REQUIRED_WITHOUT = 'required_without';
    /**
     * required_without_all:foo,bar,…
     * 仅当所有指定的字段不存在或为空时，验证下的字段才必须存在且不为空。
     */
    case REQUIRED_WITHOUT_ALL = 'required_without_all';
    /**
     * required_array_keys:foo,bar,…
     * 验证的字段必须是一个数组，并且必须至少包含指定的键。
     */
    case REQUIRED_ARRAY_KEYS = 'required_array_keys';
    /**
     * same:field
     * 给定的 field 必须与正在验证的字段匹配。
     */
    case SAME = 'same';
    /**
     * size:value
     * 验证字段的大小必须与给定的 value 匹配。
     * 对于字符串数据，value 对应于字符数。
     * 对于数字数据，value 对应于给定的整数值（属性还必须具有 numeric 或 integer 规则）。
     * 对于数组，size 对应于数组的 count。
     * 对于文件，size 对应于以千字节为单位的文件大小。 让我们看一些例子：
     * @example ------- start --------
     * // 验证一个字符串是否正好是 12 个字符长...
     * @example 'title' => 'size:12';
     *
     * // 验证提供的整数是否等于 10...
     * @example 'seats' => 'integer|size:10';
     *
     * // 验证一个数组正好有 5 个元素...
     * @example 'tags' => 'array|size:5';
     *
     * // 验证上传的文件是否正好为 512 KB...
     * @example 'image' => 'file|size:512';
     * @example ------- start --------
     */
    case SIZE = 'size';
    /**
     * 待验证字段只能是纯中文，必须为UTF-8编码
     */
    case SIMPLE_CHINESE = 'regex:~^[\x{4e00}-\x{9fa5}]+$~iu';
    /**
     * 待验证字段包含中文、字母组成，必须为UTF-8编码
     */
    case SIMPLE_CHINESE_ALPHA = 'regex:~^[\x{4e00}-\x{9fa5}a-z]+$~iu';
    /**
     * 待验证字段可以含有中文、字母、数字，短破折号（-）和下划线（_）,必须为UTF-8编码。
     */
    case SIMPLE_CHINESE_ALPHA_DASH = 'regex:~^[\x{4e00}-\x{9fa5}\w]+$~iu';
    /**
     * 待验证字段包含中文、字母、数字组成，必须为UTF-8编码
     */
    case SIMPLE_CHINESE_ALPHA_NUM = 'regex:~^[\x{4e00}-\x{9fa5}a-z0-9]+$~iu';
    /**
     * starts_with:foo,bar,…
     * 验证字段必须以给定值之一开头。
     */
    case STARTS_WITH = 'starts_with';
    /**
     * 验证字段必须是字符串。 如果您想允许该字段也为 null，则应为该字段分配 nullable 规则。
     */
    case STRING = 'string';
    /**
     * 根据 timezone_identifiers_list PHP 函数，验证中的字段必须是有效的时区标识符
     */
    case TIMEZONE = 'timezone';
    /**
     * unique:table,column
     * 验证中的字段不能存在于给定的数据库表中。
     * 指定自定义表 / 列名称：
     * 您可以指定 Eloquent 模型来确定表名，而不是直接指定表名：
     * @example 'email' => 'unique:App\Models\User,email_address'
     * @remark column 选项可用于指定字段对应的数据库列。 如果未指定 column 选项，将使用正在验证的字段的名称。
     * @example 'email' => 'unique:users,email_address'
     * 指定自定义数据库连接
     * 有时，您可能需要为验证器进行的数据库查询设置自定义连接。 为此，您可以将连接名称添加到表名称之前：
     * @example 'email' => 'unique:connection.users,email_address'
     * @tips 强制唯一规则忽略给定 ID：
     * 有时，您可能希望在唯一验证期间忽略给定的 ID。
     * 例如，考虑一个包含用户姓名、电子邮件地址和位置的 “更新个人资料” 屏幕。 您可能需要验证电子邮件地址是否唯一。
     * 但是，如果用户只更改名称字段而不更改电子邮件字段，则您不希望引发验证错误，因为用户已经是相关电子邮件地址的所有者。
     * 为了指示验证器忽略用户 ID，我们将使用 Rule 类来流畅地定义规则。
     * 在这个例子中，我们还将验证规则指定为一个数组，而不是使用 | 字符来分隔规则：
     * @example ------- start --------
     * use Illuminate\Support\Facades\Validator;
     * use Illuminate\Validation\Rule;
     * Validator::make($data, [
     *   'email' => [
     *   'required',
     *   Rule::unique('users')->ignore($user->id),
     *   ],
     * ]);
     * @example ------- end --------
     * @tips 注意：你不应该将任何用户控制的请求输入传递给 ignore 方法。
     * 相反，您应该只传递系统生成的唯一 ID，例如来自 Eloquent 模型实例的自动递增 ID 或 UUID。
     * 否则，您的应用程序将容易受到 SQL 注入攻击。
     * 除了将模型键的值传递给 ignore 方法，您还可以传递整个模型实例。 Laravel 会自动从模型中提取密钥：
     * @example Rule::unique('users')->ignore($user)
     * @remark 如果您的表使用 id 以外的主键列名，则可以在调用 ignore 方法时指定列名：
     * @example Rule::unique('users')->ignore($user->id, 'user_id')
     * @remark 默认情况下，unique 规则将检查与正在验证的属性名称匹配的列的唯一性。但是，您可以将不同的列名作为第二个参数传递给 unique 方法：
     * @example Rule::unique('users', 'email_address')->ignore($user->id),
     * 添加额外的 Where 子句：
     * 您可以通过使用 where 方法自定义查询来指定其他查询条件。例如，让我们添加一个查询条件，将查询范围限定为仅搜索 account_id 列值为 1 的记录：
     * @example 'email' => Rule::unique('users')->where(function ($query) {
     *             return $query->where('account_id', 1);
     *          });
     */
    case UNIQUE = 'unique';
    /**
     * 验证字段必须是有效的 URL。
     */
    case URL = 'url';
    /**
     * 验证字段必须是有效的 RFC 4122（版本 1、3、4 或 5）通用唯一标识符 (UUID)。
     */
    case UUID = 'uuid';

}
